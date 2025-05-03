<?php

declare(strict_types=1);

namespace Demostf\API\Controllers;

use flight\net\Request;
use flight\net\Response;

class BaseController {
    private Request $request;
    private Response $response;

    public function __construct(Request $request, Response $response) {
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * @return string[]|string
     */
    protected function query(string $name, string $default) {
        return $this->request->query[$name] ?? $default;
    }

    /**
     * @return string[]|null
     */
    protected function file(string $name): ?array {
        return $this->request->files[$name];
    }

    protected function post(string $name, string $default): string {
        return $this->request->data[$name] ?? $default;
    }

    protected function getAccessKey(): string {
        return Request::getHeader('ACCESS-KEY');
    }

    protected function getEditKey(): string {
        $key = Request::getHeader('EDIT-KEY');
        if ($key) {
            return $key;
        }
        return $this->post('key', '');
    }

    protected function json(
        mixed $data,
        int $code = 200,
        bool $encode = true,
        string $charset = 'utf-8',
        int $option = 0
    ): void {
        $json = ($encode) ? json_encode($data, $option) : $data;

        $this->response->status($code);
        $this->response->header('Content-Type', 'application/json; charset=' . $charset)
            ->write($json)
            ->send();
    }
}
