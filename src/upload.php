<?php

declare(strict_types=1);

namespace Demostf\API;

use Flight;
use flight\net\Response;

/** @var Container $container */
$container = require __DIR__ . '/init.php';

$uploadController = new Controllers\UploadController(
    $container->getRequest(),
    $container->getResponse(),
    $container->getUploadProvider()
);

Flight::route('/*', function () {
    header('Access-Control-Allow-Origin: *');

    return true;
});

Flight::route('/upload', [$uploadController, 'upload']);
Flight::route('/do_upload', [$uploadController, 'upload']);

Flight::map('error', function (\Throwable $ex) {
    $code = 500;
    if ($ex->getCode()) {
        $code = $ex->getCode();
    }
    /** @var Response $response */
    $response = Flight::response();
    if (array_key_exists($code, Response::$codes)) {
        $response->status($code);
    } else {
        $response->status(500);
    }
    $response->write($ex->getMessage())->send();
});

Flight::start();
