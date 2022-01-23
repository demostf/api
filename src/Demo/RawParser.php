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
    private string $parserPath;

    public function __construct(string $parserPath) {
        $this->parserPath = $parserPath;
    }

    /**
     * @throws Exception
     *
     * @return mixed[]|null
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
