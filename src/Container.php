<?php

declare(strict_types=1);

namespace Demostf\API;

use Demostf\API\Demo\DemoSaver;
use Demostf\API\Demo\DemoStore;
use Demostf\API\Demo\HeaderParser;
use Demostf\API\Demo\Parser;
use Demostf\API\Demo\RawParser;
use Demostf\API\Providers\AuthProvider;
use Demostf\API\Providers\ChatProvider;
use Demostf\API\Providers\DemoListProvider;
use Demostf\API\Providers\DemoProvider;
use Demostf\API\Providers\InfoProvider;
use Demostf\API\Providers\PlayerProvider;
use Demostf\API\Providers\UploadProvider;
use Demostf\API\Providers\UserProvider;
use Doctrine\DBAL\Connection;
use flight\net\Request;
use flight\net\Response;
use RandomLib\Generator;

class Container {
    private Connection $connection;
    private Generator $generator;
    private string $baseUrl;
    private string $parserPath;
    private string $storeRoot;
    private string $storeUrl;
    private string $apiRoot;
    private string $editKey;
    private Request $request;
    private Response $response;
    private string $uploadKey;
    private string $accessKey;

    public function __construct(
        Request $request,
        Response $response,
        Connection $connection,
        Generator $generator,
        string $baseUrl,
        string $parserPath,
        string $storeRoot,
        string $storeUrl,
        string $apiRoot,
        string $editKey,
        string $uploadKey,
        string $accessKey,
    ) {
        $this->request = $request;
        $this->response = $response;
        $this->connection = $connection;
        $this->generator = $generator;
        $this->baseUrl = $baseUrl;
        $this->parserPath = $parserPath;
        $this->storeRoot = $storeRoot;
        $this->storeUrl = $storeUrl;
        $this->apiRoot = $apiRoot;
        $this->editKey = $editKey;
        $this->uploadKey = $uploadKey;
        $this->accessKey = $accessKey;
    }

    public function getAuthProvider(): AuthProvider {
        return new AuthProvider($this->connection, $this->generator);
    }

    public function getChatProvider(): ChatProvider {
        return new ChatProvider($this->connection);
    }

    public function getDemoListProvider(): DemoListProvider {
        return new DemoListProvider($this->connection);
    }

    public function getDemoProvider(): DemoProvider {
        return new DemoProvider($this->connection, $this->getUserProvider());
    }

    public function getInfoProvider(): InfoProvider {
        return new InfoProvider($this->connection);
    }

    public function getPlayerProvider(): PlayerProvider {
        return new PlayerProvider($this->connection);
    }

    public function getRawParser(): RawParser {
        return new RawParser($this->getParserPath());
    }

    public function getUploadProvider(): UploadProvider {
        return new UploadProvider(
            $this->connection,
            $this->baseUrl,
            new HeaderParser(),
            new Parser($this->getRawParser()),
            $this->getDemoStore(),
            $this->getUserProvider(),
            $this->getDemoProvider(),
            new DemoSaver(
                $this->getPlayerProvider(),
                $this->getChatProvider(),
                $this->getUserProvider(),
                $this->getDemoProvider(),
                $this->getConnection()
            ),
            $this->getUploadKey()
        );
    }

    public function getDemoStore(): DemoStore {
        return new DemoStore($this->storeRoot, $this->storeUrl);
    }

    public function getUserProvider(): UserProvider {
        return new UserProvider($this->connection, $this->generator);
    }

    public function getBaseUrl(): string {
        return $this->baseUrl;
    }

    public function getParserPath(): string {
        return $this->parserPath;
    }

    public function getStoreRoot(): string {
        return $this->storeRoot;
    }

    public function getStoreUrl(): string {
        return $this->storeUrl;
    }

    public function getApiRoot(): string {
        return $this->apiRoot;
    }

    public function getEditKey(): string {
        return $this->editKey;
    }

    public function getConnection(): Connection {
        return $this->connection;
    }

    public function getRequest(): Request {
        return $this->request;
    }

    public function getResponse(): Response {
        return $this->response;
    }

    public function getUploadKey(): string {
        return $this->uploadKey;
    }

    public function getAccessKey(): string {
        return $this->accessKey;
    }
}
