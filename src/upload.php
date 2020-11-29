<?php

declare(strict_types=1);

namespace Demostf\API;

use Demostf\API\Error\InvalidHashException;
use Demostf\API\Error\InvalidKeyException;
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
    if ($ex instanceof InvalidKeyException) {
        $code = 401;
    } elseif ($ex instanceof InvalidHashException) {
        $code = 412;
    }
    /** @var Response $response */
    $response = Flight::response()->status($code);
    $response->write($ex->getMessage())->send();
});

Flight::start();
