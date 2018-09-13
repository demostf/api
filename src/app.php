<?php

declare(strict_types=1);

namespace Demostf\API;

use Flight;

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
$tempController = new Controllers\TempController($container->getApiRoot() . '/temp/');

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

Flight::route('/temp/@hash', [$tempController, 'serve']);

Flight::start();
