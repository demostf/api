<?php

declare(strict_types=1);

namespace Demostf\API\Controllers;

use Demostf\API\Providers\UploadProvider;
use Exception;
use Flight;
use flight\net\Request;
use flight\net\Response;

class UploadController extends BaseController {
    private $uploadProvider;

    public function __construct(Request $request, Response $response, UploadProvider $uploadProvider) {
        parent::__construct($request, $response);
        $this->uploadProvider = $uploadProvider;
    }

    public function upload() {
        $key = (string) $this->post('key', '');
        $red = (string) $this->post('red', 'RED');
        $blu = (string) $this->post('blu', 'BLU');
        $name = (string) $this->post('name', 'Unnamed');
        $demo = $this->file('demo');
        if (null === $demo) {
            echo 'No demo uploaded';

            return;
        }
        $demoFile = $demo['tmp_name'];

        try {
            $result = $this->uploadProvider->upload($key, $red, $blu, $name, $demoFile);
            if ('Invalid key' === $result) {
                Flight::response()->status(401)->write($result)->send();
            } else {
                echo $result;
            }
        } catch (Exception $e) {
            Flight::response()
                ->status(500)
                ->write($e->getMessage())
                ->send();
        }
    }
}
