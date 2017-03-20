<?php namespace Demostf\API;

use Flight;

require_once __DIR__ . '/init.php';


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

$demoProvider = new Providers\DemoProvider($db);

$factory = new \RandomLib\Factory;
$generator = $factory->getMediumStrengthGenerator();
$authProvider = new Providers\AuthProvider($db, $generator);
$userProvider = new Providers\UserProvider($db, $generator);
$infoProvider = new Providers\InfoProvider($db);
$demoController = new Controllers\DemoController($demoProvider);
$authController = new Controllers\AuthController($userProvider, $authProvider, $host);
$userController = new Controllers\UserController($userProvider);
$infoController = new Controllers\InfoController($infoProvider);

Flight::route('/*', function () {
	header('Access-Control-Allow-Origin: *');
	return true;
});

Flight::route('/auth/*', function () {
	session_start();
	return true;
});

Flight::route('/', function () {
	echo 'hello world!';
});

Flight::route('/maps', [$infoController, 'listMaps']);
Flight::route('/stats', [$infoController, 'stats']);

Flight::route('/demos', [$demoController, 'listDemos']);
Flight::route('/demos/@id', [$demoController, 'get']);
Flight::route('/demos/@id/chat', [$demoController, 'chat']);
Flight::route('/profiles/@steamid', [$demoController, 'listProfile']);
Flight::route('/uploads/@steamid', [$demoController, 'listUploads']);

Flight::route('/users/search', [$userController, 'search']);
Flight::route('/users/@steamid', [$userController, 'get']);

Flight::route('/auth/token', [$authController, 'token']);
Flight::route('/auth/get/@token', [$authController, 'get']);
Flight::route('/auth/handle/@token', [$authController, 'handle']);
Flight::route('/auth/login/@token', [$authController, 'login']);
Flight::route('/auth/logout/@token', [$authController, 'logout']);

Flight::start();
