<?php

declare(strict_types=1);

namespace Demostf\API\Demo;

use Exception;
use GuzzleHttp\Exception\RequestException;

/**
 * Wrapper around demo.js parser.
 *
 * Doesn't do any post-processing on the result
 */
class RawParser {
    /** @var string */
    private $parserPath;

    public function __construct(string $parserPath) {
        $this->parserPath = $parserPath;
    }

    /**
     * @param string $path
     * @return array|null
     * @throws Exception
     */
    public function parse(string $path): ?array {
        try {
            $command = $this->parserPath . ' ' . escapeshellarg($path);
            $output = shell_exec($command);
            $result = \GuzzleHttp\json_decode($output, true);
            if (null === $result) {
                throw new Exception('Failed to parse demo, unexpected result from parser');
            } else {
                return $result;
            }
        } catch (RequestException $e) {
            throw new Exception('Failed to parse demo, ' . $e->getMessage());
        }
    }
}
