<?php

$autoloader = require __DIR__ . '/vendor/autoload.php';
$autoloader->setPsr4('Providers\\', __DIR__ . '/Providers');
$autoloader->setPsr4('Demo\\', __DIR__ . '/Demo');
$autoloader->setPsr4('Controllers\\', __DIR__ . '/Controllers');

if (!getenv('DB_TYPE')) {
	Dotenv::load(__DIR__);
}

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

$demoProvider = new \Providers\DemoProvider($db);

$factory = new \RandomLib\Factory;
$generator = $factory->getMediumStrengthGenerator();
$authProvider = new \Providers\AuthProvider($db, $generator);
$userProvider = new \Providers\UserProvider($db, $generator);
$demoController = new \Controllers\DemoController($demoProvider);
$authController = new \Controllers\AuthController($userProvider, $authProvider);
$userController = new \Controllers\UserController($userProvider);

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

Flight::route('/maps', [$demoController, 'listMaps']);
Flight::route('/stats', [$demoController, 'stats']);

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
