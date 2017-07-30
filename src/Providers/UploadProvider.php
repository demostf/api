<?php

declare(strict_types=1);

namespace Demostf\API\Providers;

use Demostf\API\Data\ParsedDemo;
use Demostf\API\Data\Upload;
use Demostf\API\Demo\DemoSaver;
use Demostf\API\Demo\DemoStore;
use Demostf\API\Demo\Header;
use Demostf\API\Demo\HeaderParser;
use Demostf\API\Demo\Parser;
use Doctrine\DBAL\Connection;
use RandomLib\Generator;

class UploadProvider extends BaseProvider {
    /** @var Generator */
    private $generator;
    /** @var HeaderParser */
    private $headerParser;
    /** @var Parser */
    private $parser;
    /** @var DemoStore */
    private $store;
    /** @var UserProvider */
    private $userProvider;
    /** @var DemoProvider */
    private $demoProvider;
    /** @var DemoSaver */
    private $demoSaver;
    private $baseUrl;

    public function __construct(
        Connection $db,
                                string $baseUrl,
                                HeaderParser $headerParser,
                                Parser $parser,
                                DemoStore $store,
                                UserProvider $userProvider,
                                DemoProvider $demoProvider,
                                DemoSaver $demoSaver
    ) {
        parent::__construct($db);
        $this->baseUrl = $baseUrl;
        $this->headerParser = $headerParser;
        $this->parser = $parser;
        $this->store = $store;
        $this->userProvider = $userProvider;
        $this->demoProvider = $demoProvider;
        $this->demoSaver = $demoSaver;
    }

    public function upload(string $key, string $red, string $blu, string $name, string $demoFile): string {
        $user = $this->userProvider->byKey($key);
        if (!$user) {
            return 'Invalid key';
        }

        $hash = hash_file('md5', $demoFile);

        $existingDemo = $this->demoProvider->demoIdByHash($hash);
        if ($existingDemo) {
            return 'STV available at: ' . $this->baseUrl . '/' . $existingDemo;
        }

        $header = $this->headerParser->parseHeader($demoFile);
        $error = $this->validateHeader(filesize($demoFile), $header);
        if ($error) {
            return $error;
        }

        $parsed = $this->parser->analyse($demoFile);

        $error = $this->validateParsed($header, $parsed);
        if ($error) {
            return $error;
        }

        $storedDemo = $this->store->store($demoFile, $hash . '_' . $name);
        $upload = new Upload($name, $red, $blu, $user->getId(), $hash);

        $id = $this->demoSaver->saveDemo($parsed, $header, $storedDemo, $upload);

        return 'STV available at: ' . $this->baseUrl . '/' . $id;
    }

    public function validateHeader(int $size, Header $header) {
        if ($size < 1024) {
            return 'Demos needs to be at least 1KB is size';
        }

        if ($size > 100 * 1024 * 1024) {
            return 'Demos cant be more than 100MB in size';
        }

        if ($header->getDuration() > (60 * 60)) {
            return 'Demos cant be longer than one hour';
        }

        return null;
    }

    public function validateParsed(Header $header, ParsedDemo $parsedDemo) {
        $rounds = $parsedDemo->getRedScore() + $parsedDemo->getBlueScore();
        if ($rounds === 0 && $header->getDuration() < (5 * 60)) {
            return 'Demos must be at least 5 minutes long';
        }

        return null;
    }
}
