<?php

declare(strict_types=1);

namespace Demostf\API\Providers;

use const DATE_ATOM;
use Demostf\API\Data\DemoPlayer;
use Demostf\API\Demo\Demo;
use Doctrine\DBAL\Connection;
use PDO;

class DemoProvider extends BaseProvider {
    const VERSION = 4;

    private UserProvider $userProvider;

    public function __construct(Connection $connection, UserProvider $userProvider) {
        parent::__construct($connection);

        $this->userProvider = $userProvider;
    }

    private function fetchDemo(int $id): ?Demo {
        $query = $this->getQueryBuilder();
        $query->select('*')
            ->from('demos')
            ->where($query->expr()->eq('id', $query->createNamedParameter($id, PDO::PARAM_INT)));
        $row = $query->execute()->fetch();

        return $row ? Demo::fromRow($row) : null;
    }

    public function get(int $id, bool $fetchDetails = true): ?Demo {
        // sql magic
        $sql = 'WITH demokills AS (SELECT attacker_id, assister_id, victim_id FROM kills WHERE demo_id = ?)
		SELECT players.id, user_id, players.name, team, class, users.steamid, users.avatar,
		(SELECT COUNT(*) FROM demokills WHERE attacker_id=players.user_id) AS kills,
		(SELECT COUNT(*) FROM demokills WHERE assister_id=players.user_id) AS assists,
		(SELECT COUNT(*) FROM demokills WHERE victim_id=players.user_id) AS deaths
		FROM players
		INNER JOIN users ON players.user_id = users.id
		WHERE demo_id = ?';

        $demo = $this->fetchDemo($id);
        if (null === $demo) {
            return null;
        }

        if ($fetchDetails) {
            $uploader = $this->userProvider->getById($demo->getUploader());
            $playerQuery = $this->connection->executeQuery($sql, [$demo->getId(), $demo->getId()]);
            $players = $playerQuery->fetchAll(PDO::FETCH_ASSOC);

            $demo->setUploaderUser($uploader);
            $uniquePlayers = [];
            foreach ($players as $player) {
                $key = $player['steamid'] . $player['team'];
                if (!isset($uniquePlayers[$key])) {
                    $uniquePlayers[$key] = $player;
                }
            }
            $demo->setPlayers(array_map(function ($player) {
                return DemoPlayer::fromRow($player);
            }, array_values($uniquePlayers)));
        }

        return $demo;
    }

    public function demoIdByHash(string $hash): int {
        $query = $this->getQueryBuilder();
        $query->select('id')
            ->from('demos')
            ->where($query->expr()->eq('hash', $query->createNamedParameter($hash)));

        return (int) $query->execute()->fetchColumn();
    }

    public function storeDemo(Demo $demo, string $backend, string $path): int {
        $query = $this->getQueryBuilder();
        $query->insert('demos')
            ->values([
                'name' => $query->createNamedParameter($demo->getName()),
                'url' => $query->createNamedParameter($demo->getUrl()),
                'map' => $query->createNamedParameter($demo->getMap()),
                'red' => $query->createNamedParameter($demo->getRed()),
                'blu' => $query->createNamedParameter($demo->getBlue()),
                'uploader' => $query->createNamedParameter($demo->getUploader(), PDO::PARAM_INT),
                'duration' => $query->createNamedParameter((int) $demo->getDuration(), PDO::PARAM_INT),
                'created_at' => $query->createNamedParameter($demo->getTime()->format(DATE_ATOM)),
                'updated_at' => 'now()',
                'backend' => $query->createNamedParameter($backend),
                'path' => $query->createNamedParameter($path),
                '"scoreBlue"' => $query->createNamedParameter($demo->getBlueScore(), PDO::PARAM_INT),
                '"scoreRed"' => $query->createNamedParameter($demo->getRedScore(), PDO::PARAM_INT),
                'version' => $query->createNamedParameter(self::VERSION, PDO::PARAM_INT),
                'server' => $query->createNamedParameter($demo->getServer()),
                'nick' => $query->createNamedParameter($demo->getNick()),
                '"playerCount"' => $query->createNamedParameter($demo->getPlayerCount(), PDO::PARAM_INT),
                'hash' => $query->createNamedParameter($demo->getHash()),
            ])
            ->execute();

        return (int) $this->connection->lastInsertId();
    }

    public function setDemoUrl(int $id, string $backend, string $url, string $path): void {
        $query = $this->getQueryBuilder();
        $query->update('demos')
            ->set('backend', $query->createNamedParameter($backend))
            ->set('url', $query->createNamedParameter($url))
            ->set('path', $query->createNamedParameter($path))
            ->where($query->expr()->eq('id', $query->createNamedParameter($id, PDO::PARAM_INT)))
            ->execute();
    }
}
