<?php declare(strict_types=1);

namespace Demostf\API\Providers;

use Demostf\API\Demo\DemoSaver;
use Demostf\API\Demo\DemoStore;
use Demostf\API\Demo\HeaderParser;
use Demostf\API\Demo\Parser;
use Demostf\API\Demo\RawParser;
use Doctrine\DBAL\Connection;
use RandomLib\Generator;

class Container {
	private $connection;
	private $generator;
	private $baseUrl;
	private $parserUrl;
	private $storeRoot;
	private $storeUrl;
	private $apiRoot;

	public function __construct(
		Connection $connection,
		Generator $generator,
		string $baseUrl,
		string $parserUrl,
		string $storeRoot,
		string $storeUrl,
		string $apiRoot
	) {
		$this->connection = $connection;
		$this->generator = $generator;
		$this->baseUrl = $baseUrl;
		$this->parserUrl = $parserUrl;
		$this->storeRoot = $storeRoot;
		$this->storeUrl = $storeUrl;
		$this->apiRoot = $apiRoot;
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
		return new DemoProvider($this->connection);
	}

	public function getInfoProvider(): InfoProvider {
		return new InfoProvider($this->connection);
	}

	public function getKillProvider(): KillProvider {
		return new KillProvider($this->connection);
	}

	public function getPlayerProvider(): PlayerProvider {
		return new PlayerProvider($this->connection);
	}

	public function getUploadProvider(): UploadProvider {
		return new UploadProvider(
			$this->connection,
			$this->baseUrl,
			new HeaderParser(),
			new Parser(new RawParser($this->parserUrl)),
			new DemoStore($this->storeRoot, $this->storeUrl),
			$this->getUserProvider(),
			$this->getDemoProvider(),
			new DemoSaver(
				$this->getKillProvider(),
				$this->getPlayerProvider(),
				$this->getChatProvider(),
				$this->getUserProvider(),
				$this->getDemoProvider()
			)
		);
	}

	public function getUserProvider(): UserProvider {
		return new UserProvider($this->connection, $this->generator);
	}

	public function getBaseUrl(): string {
		return $this->baseUrl;
	}

	public function getParserUrl(): string {
		return $this->parserUrl;
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
}
