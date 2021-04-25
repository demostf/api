<?php

declare(strict_types=1);

namespace Demostf\API;

use Demostf\API\Error\InvalidHashException;
use Demostf\API\Error\InvalidKeyException;
use Flight;
use flight\net\Response;

/** @var Container $container */
$container = require __DIR__ . '/init.php';

$demoController = new Controllers\DemoController(
    $container->getRequest(),
    $container->getResponse(),
    $container->getDemoProvider(),
    $container->getChatProvider(),
    $container->getDemoListProvider(),
    $container->getDemoStore(),
    $container->getEditKey()
);
$authController = new Controllers\AuthController(
    $container->getRequest(),
    $container->getResponse(),
    $container->getUserProvider(),
    $container->getAuthProvider(),
    $container->getBaseUrl(),
    $container->getApiRoot()
);
$userController = new Controllers\UserController($container->getRequest(), $container->getResponse(),
    $container->getUserProvider());
$infoController = new Controllers\InfoController($container->getRequest(), $container->getResponse(),
    $container->getInfoProvider());

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
Flight::route('/demos/@id/url', [$demoController, 'setDemoUrl']);
Flight::route('/profiles/@steamid', [$demoController, 'listProfile']);
Flight::route('/uploads/@steamid', [$demoController, 'listUploads']);

Flight::route('/users/search', [$userController, 'search']);
Flight::route('/users/@steamid', [$userController, 'get']);

Flight::route('/auth/token', [$authController, 'token']);
Flight::route('/auth/get/@token', [$authController, 'get']);
Flight::route('/auth/handle/@token', [$authController, 'handle']);
Flight::route('/auth/login/@token', [$authController, 'login']);
Flight::route('/auth/logout/@token', [$authController, 'logout']);

Flight::map('error', function (\Throwable $ex) {
    $code = 500;
    if ($ex->getCode()) {
        $code = $ex->getCode();
    }
    $response = Flight::response();
    if (array_key_exists($code, Response::$codes)) {
        $response->status($code);
    } else {
        $response->status(500);
    }
    $response->write($ex->getMessage())->send();
});

Flight::start();
