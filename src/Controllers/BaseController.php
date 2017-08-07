<?php

declare(strict_types=1);

namespace Demostf\API\Controllers;

use flight\net\Request;
use flight\net\Response;

class BaseController {
    private $request;
    private $response;

    public function __construct(Request $request, Response $response) {
        $this->request = $request;
        $this->response = $response;
    }

    protected function query($name, $default) {
        return isset($this->request->query[$name]) ? $this->request->query[$name] : $default;
    }

    protected function file($name) {
        return $this->request->files[$name];
    }

    protected function post($name, $default = null) {
        return isset($this->request->data[$name]) ? $this->request->data[$name] : $default;
    }

    protected function json($data, $code = 200, $encode = true, $charset = 'utf-8', $option = 0) {
        $json = ($encode) ? json_encode($data, $option) : $data;

        $this->response
            ->status($code)
            ->header('Content-Type', 'application/json; charset=' . $charset)
            ->write($json)
            ->send();
    }
}
