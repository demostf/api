<?php namespace Demo;

class StoredDemo {
	/**
	 * @var string
	 */
	private $backend;

	/**
	 * @var string
	 */
	private $path;

	/**
	 * @var string
	 */
	private $url;

	/**
	 * @param string $backend
	 * @param string $path
	 * @param string $url
	 */
	public function __construct($backend, $path, $url) {
		$this->backend = $backend;
		$this->path = $path;
		$this->url = $url;
	}

	/**
	 * @return string
	 */
	public function getBackend() {
		return $this->backend;
	}

	/**
	 * @return string
	 */
	public function getPath() {
		return $this->path;
	}

	/**
	 * @return string
	 */
	public function getUrl() {
		return $this->url;
	}
}
