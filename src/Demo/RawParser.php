<?php declare(strict_types=1);

namespace Demostf\API\Demo;

use GuzzleHttp\Client;

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
		$client = new Client();
		$response = $client->post($this->parserUrl, [
			'body' => fopen($path, 'r')
		]);
		return json_decode($response->getBody()->getContents(), true);
	}
}
