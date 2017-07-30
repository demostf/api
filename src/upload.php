<?php declare(strict_types=1);

namespace Demostf\API;

use Demostf\API\Providers\Container;
use Flight;

/** @var Container $container */
$container = require __DIR__ . '/init.php';

$uploadController = new Controllers\UploadController($container->getUploadProvider());

Flight::route('/*', function () {
    header('Access-Control-Allow-Origin: *');
    return true;
});

Flight::route('/upload', [$uploadController, 'upload']);
Flight::route('/do_upload', [$uploadController, 'upload']);

Flight::start();
