<?php

declare(strict_types=1);

namespace Demostf\API\Test;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;

abstract class TestCase extends \PHPUnit\Framework\TestCase {
    /** @var Connection */
    private $database;

    protected function getDatabaseConnection() {
        if (!$this->database instanceof Connection) {
            $connectionParams = [
                'dbname' => getenv('DB_DATABASE'),
                'user' => getenv('DB_USERNAME'),
                'password' => getenv('DB_PASSWORD'),
                'host' => getenv('DB_HOST'),
                'port' => getenv('DB_PORT'),
                'driver' => getenv('DB_TYPE'),
            ];
            if ('pgsql' === $connectionParams['driver']) {
                $connectionParams['driver'] = 'pdo_pgsql';
            }
            $this->database = DriverManager::getConnection($connectionParams);
        }

        return $this->database;
    }

    public function setUp(): void {
        parent::setUp();
    }

    public function tearDown(): void {
        $this->clearDatabase();
        parent::tearDown();
    }

    private function clearDatabase() {
        if ($this->database instanceof Connection) {
            $tables = $this->database->getSchemaManager()->listTables();
            foreach ($tables as $table) {
                $this->truncateTable($table->getName());
            }
        }
    }

    private function truncateTable(string $tableName) {
        $sql = sprintf('TRUNCATE TABLE %s;', $tableName);
        $this->getDatabaseConnection()->query($sql);
    }

    protected function getRandomGenerator() {
        $factory = new \RandomLib\Factory();

        return $factory->getMediumStrengthGenerator();
    }

    protected function getSteamId($steamId, $name) {
        $steamId = new \SteamId($steamId, false);
        $closure = \Closure::bind(function ($steamId) use ($name) {
            $steamId->nickname = $name;
            $steamId->imageUrl = 'foo';
        }, null, $steamId);
        $closure($steamId);

        return $steamId;
    }
}
