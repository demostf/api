<?php declare(strict_types=1);

namespace Demostf\API\Demo;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Wrapper around demo.js parser
 *
 * Doesn't do any post-processing on the result
 */
class RawParser {
	/** @var string */
	private $parserUrl;

	public function __construct(string $parserUrl) {
		$this->parserUrl = $parserUrl;
	}

	public function parse(string $path): ?array {
		try {
			$client = new Client();
			$response = $client->post($this->parserUrl, [
				'body' => fopen($path, 'r')
			]);
			$result = json_decode($response->getBody()->getContents(), true);
			if (is_null($result)) {
				throw new \Exception('Failed to parse demo, unexpected result from parser');
			} else {
				return $result;
			}
		} catch (GuzzleException $e) {
			if (strpos($e->getMessage(), 'cURL error 52') !== false) {
				throw new \Exception('Failed to parse demo, can\'t reach demo parser');
			}
			throw new \Exception('Failed to parse demo, ' . $e->getMessage());
		}
	}
}
