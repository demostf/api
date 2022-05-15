<?php

declare(strict_types=1);

use Demostf\API\Container;
use Doctrine\DBAL\DriverManager;
use Dotenv\Dotenv;

function get_magic_quotes_gpc(): bool {
    return false;
}

$autoloader = require __DIR__ . '/../vendor/autoload.php';

if (!getenv('DB_TYPE')) {
    Dotenv::createImmutable(__DIR__ . '/../')->safeLoad();
}

$driver = getenv('DB_TYPE') ?: '';
if ($driver === 'pgsql') {
    $driver = 'pdo_pgsql';
}

$availableDrivers = DriverManager::getAvailableDrivers();
if (!in_array($driver, $availableDrivers)) {
    throw new \Exception("Unsupported driver " . $driver);
}
/** @var key-of<DriverManager::DRIVER_MAP> $driver */

$connectionParams = [
    'dbname' => getenv('DB_DATABASE') ?: '',
    'user' => getenv('DB_USERNAME') ?: '',
    'password' => getenv('DB_PASSWORD') ?: '',
    'host' => getenv('DB_HOST') ?: '',
    'port' => (int) getenv('DB_PORT'),
    'driver' => $driver,
];

$db = DriverManager::getConnection($connectionParams);
$host = getenv('BASE_HOST') ?: '';
$storeRoot = getenv('DEMO_ROOT') ?: '';
$storeHost = getenv('DEMO_HOST') ?: '';
$parserPath = getenv('PARSER_PATH') ?: '';
$appRoot = getenv('APP_ROOT') ?: '';
$editKey = getenv('EDIT_SECRET') ?: '';
$uploadKey = getenv('UPLOAD_KEY') ?: '';

$factory = new \RandomLib\Factory();
$generator = $factory->getMediumStrengthGenerator();

$container = new Container(
    Flight::request(),
    Flight::response(),
    $db,
    $generator,
    'https://' . $host,
    $parserPath,
    $storeRoot,
    $storeHost,
    $appRoot,
    $editKey,
    $uploadKey
);

return $container;
