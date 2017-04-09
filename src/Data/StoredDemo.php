<?php declare(strict_types=1);

namespace Demostf\API\Data;

class StoredDemo {
	/** @var string */
	private $url;
	/** @var string */
	private $backend;
	/** @var string */
	private $path;

	public function __construct(string $url, string $backend, string $path) {
		$this->url = $url;
		$this->backend = $backend;
		$this->path = $path;
	}

	public function getUrl(): string {
		return $this->url;
	}

	public function getBackend(): string {
		return $this->backend;
	}

	public function getPath(): string {
		return $this->path;
	}
}
