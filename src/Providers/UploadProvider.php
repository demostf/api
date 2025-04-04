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
use Demostf\API\Error\InvalidKeyException;
use Doctrine\DBAL\Connection;

class UploadProvider extends BaseProvider {
    private HeaderParser $headerParser;
    private Parser $parser;
    private DemoStore $store;
    private UserProvider $userProvider;
    private DemoProvider $demoProvider;
    private DemoSaver $demoSaver;
    private string $baseUrl;
    private string $uploadKey;

    public function __construct(
        Connection $db,
        string $baseUrl,
        HeaderParser $headerParser,
        Parser $parser,
        DemoStore $store,
        UserProvider $userProvider,
        DemoProvider $demoProvider,
        DemoSaver $demoSaver,
        string $uploadKey
    ) {
        parent::__construct($db);
        $this->baseUrl = $baseUrl;
        $this->headerParser = $headerParser;
        $this->parser = $parser;
        $this->store = $store;
        $this->userProvider = $userProvider;
        $this->demoProvider = $demoProvider;
        $this->demoSaver = $demoSaver;
        $this->uploadKey = $uploadKey;
    }

    public function upload(string $key, string $red, string $blu, string $name, string $demoFile, bool $private): string {
        $nameParts = explode('/', $name);
        $name = array_pop($nameParts);
        $name = str_replace('%', '_', $name);

        $user = $this->userProvider->byKey($key);
        if (!$user || ('' !== $this->uploadKey && $this->uploadKey !== $key)) {
            throw new InvalidKeyException('Invalid key');
        }

        if (!mb_check_encoding($red, 'UTF-8')) {
            $red = 'RED';
        }

        if (!mb_check_encoding($blu, 'UTF-8')) {
            $blu = 'BLU';
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

        try {
            $storedDemo = $this->store->store($demoFile, $hash . '_' . $name);
            $upload = new Upload($name, $red, $blu, $user->getId(), $hash, $private);

            $id = $this->demoSaver->saveDemo($parsed, $header, $storedDemo, $upload);
        } catch (\Exception $e) {
            $this->store->removeByName($name);
            return 'Error while saving demo: ' . $e->getMessage();
        }

        return 'STV available at: ' . $this->baseUrl . '/' . $id;
    }

    public function validateHeader(int $size, Header $header): ?string {
        if ($size < 1024) {
            return 'Demos needs to be at least 1KB is size';
        }

        if ($size > 200 * 1024 * 1024) {
            return 'Demos cant be more than 200MB in size';
        }

        if ($header->getDuration() > (90 * 60)) {
            return 'Demos cant be longer than one and a half hour';
        }

        return null;
    }

    public function validateParsed(Header $header, ParsedDemo $parsedDemo): ?string {
        $rounds = $parsedDemo->getRedScore() + $parsedDemo->getBlueScore();
        if (0 === $rounds && $header->getDuration() < (15 * 60)) {
            return 'Demos must be at least 5 minutes long';
        }

        return null;
    }
}
