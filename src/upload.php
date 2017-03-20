<?php namespace Demostf\API;

require_once __DIR__ . '/init.php';

$connectionParams = array(
	'dbname' => getenv('DB_DATABASE'),
	'user' => getenv('DB_USERNAME'),
	'password' => getenv('DB_PASSWORD'),
	'host' => getenv('DB_HOST'),
	'driver' => getenv('DB_TYPE'),
);
if ($connectionParams['driver'] === 'pgsql') {
	$connectionParams['driver'] = 'pdo_pgsql';
}
$db = \Doctrine\DBAL\DriverManager::getConnection($connectionParams);
$host = getenv('BASE_HOST');
$storeRoot = getenv('DEMO_ROOT');

$demoProvider = new Providers\DemoProvider($db);

$factory = new \RandomLib\Factory;
$generator = $factory->getMediumStrengthGenerator();
$userProvider = new Providers\UserProvider($db, $generator);
$store = new Demo\DemoStore($storeRoot, 'static.' . $host);
$uploadController = new Controllers\UploadController($demoProvider, $userProvider, new Demo\Parser(), $store);
$uploadController->upload();
