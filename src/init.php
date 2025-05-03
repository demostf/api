<?php

declare(strict_types=1);

use Demostf\API\Container;
use Doctrine\DBAL\DriverManager;
use Dotenv\Dotenv;

function get_magic_quotes_gpc(): bool {
    return false;
}

$autoloader = require __DIR__ . '/../vendor/autoload.php';


function getEnvVar(string $name): string {
    $var = getenv($name) ?: '';
    if (str_contains($var, '$CREDENTIALS_DIRECTORY')) {
        $credentialsDirectory = getenv('CREDENTIALS_DIRECTORY') ?: '';
        $path = str_replace('$CREDENTIALS_DIRECTORY', $credentialsDirectory, $var);
        $var = file_get_contents($path);
    }
    return trim($var);
}

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

$dbPassword = getEnvVar('DB_PASSWORD');

$connectionParams = [
    'dbname' => getEnvVar('DB_DATABASE'),
    'user' => getEnvVar('DB_USERNAME'),
    'host' => getEnvVar('DB_HOST'),
    'port' => (int) getEnvVar('DB_PORT'),
    'driver' => $driver,
];

if ($dbPassword) {
    $connectionParams['password'] = $dbPassword;
}

$db = DriverManager::getConnection($connectionParams);
$host = getEnvVar('BASE_HOST');
$storeRoot = getEnvVar('DEMO_ROOT');
$storeHost = getEnvVar('DEMO_HOST');
$parserPath = getEnvVar('PARSER_PATH');
$appRoot = getEnvVar('APP_ROOT');
$editKey = getEnvVar('EDIT_SECRET');
$uploadKey = getEnvVar('UPLOAD_KEY');
$accessKey = getEnvVar('ACCESS_KEY');

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
    $uploadKey,
    $accessKey,
);

return $container;
