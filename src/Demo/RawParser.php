<?php

declare(strict_types=1);

namespace Demostf\API\Demo;

use Demostf\API\Controllers\TempController;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use function GuzzleHttp\Psr7\stream_for;

/**
 * Wrapper around demo.js parser.
 *
 * Doesn't do any post-processing on the result
 */
class RawParser {
    /** @var string */
    private $parserUrl;

    private $tempController;

    public function __construct(string $parserUrl, TempController $tempController) {
        $this->parserUrl = $parserUrl;
        $this->tempController = $tempController;
    }

    public function parse(string $path): ?array {
        $key = md5($path);
        $url = $this->tempController->register($key, $path);
        try {
            $client = new Client();
            echo($url);
            $response = $client->post($this->parserUrl, [
                'body' => stream_for($url),
                'headers' => [
                    'Content-Type' => 'application/octet-stream'
                ]
            ]);
            $result = json_decode($response->getBody()->getContents(), true);
            $this->tempController->unregister($key);
            if (null === $result) {
                throw new \Exception('Failed to parse demo, unexpected result from parser');
            } else {
                return $result;
            }
        } catch (RequestException $e) {
            $this->tempController->unregister($key);
            if (strpos($e->getMessage(), 'cURL error 52') !== false) {
                throw new \Exception('Failed to parse demo, can\'t reach demo parser');
            }
            throw new \Exception('Failed to parse demo, ' . $e->getMessage() . ' ' . $url);
        }
    }
}
