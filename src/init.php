<?php declare(strict_types=1);

use Demostf\API\Providers\Container;

$autoloader = require __DIR__ . '/../vendor/autoload.php';

if (!getenv('DB_TYPE')) {
	Dotenv::load(__DIR__ . '/../');
}

$connectionParams = array(
	'dbname' => getenv('DB_DATABASE'),
	'user' => getenv('DB_USERNAME'),
	'password' => getenv('DB_PASSWORD'),
	'host' => getenv('DB_HOST'),
	'port' => getenv('DB_PORT'),
	'driver' => getenv('DB_TYPE'),
);
if ($connectionParams['driver'] === 'pgsql') {
	$connectionParams['driver'] = 'pdo_pgsql';
}
$db = \Doctrine\DBAL\DriverManager::getConnection($connectionParams);
$host = getenv('BASE_HOST');
$storeRoot = getenv('DEMO_ROOT');
$storeHost = getenv('DEMO_HOST');
$parserUrl = getenv('PARSER_URL');
$appRoot = getenv('APP_ROOT');

$factory = new \RandomLib\Factory;
$generator = $factory->getMediumStrengthGenerator();

$container = new Container(
	$db,
	$generator,
	'https://' . $host,
	$parserUrl,
	$storeRoot,
	'https://' . $storeHost,
	$appRoot
);

return $container;
