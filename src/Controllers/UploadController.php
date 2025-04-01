<?php

declare(strict_types=1);

namespace Demostf\API\Controllers;

use Demostf\API\Providers\UploadProvider;
use flight\net\Request;
use flight\net\Response;

class UploadController extends BaseController {
    private UploadProvider $uploadProvider;

    public function __construct(Request $request, Response $response, UploadProvider $uploadProvider) {
        parent::__construct($request, $response);
        $this->uploadProvider = $uploadProvider;
    }

    public function upload(): void {
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
        $private = $this->post('private', '0') === '1';

        echo $this->uploadProvider->upload($key, $red, $blu, $name, $demoFile, $private);
    }
}
