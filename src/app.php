<?php declare(strict_types=1);

namespace Demostf\API;

use Demostf\API\Providers\Container;
use Flight;

/** @var Container $container */
$container = require __DIR__ . '/init.php';

$demoController = new Controllers\DemoController(
	$container->getDemoProvider(),
	$container->getChatProvider(),
	$container->getDemoListProvider()
);
$authController = new Controllers\AuthController(
	$container->getUserProvider(),
	$container->getAuthProvider(),
	$container->getBaseUrl(),
	$container->getApiRoot()
);
$userController = new Controllers\UserController($container->getUserProvider());
$infoController = new Controllers\InfoController($container->getInfoProvider());

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
