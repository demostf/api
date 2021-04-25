<?php

declare(strict_types=1);

use Demostf\API\Container;
use Doctrine\DBAL\DriverManager;

function get_magic_quotes_gpc(): bool {
    return false;
}

$autoloader = require __DIR__ . '/../vendor/autoload.php';

if (!getenv('DB_TYPE')) {
    Dotenv::load(__DIR__ . '/../');
}

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
