<?php namespace Demostf\API\Demo;

class DemoStore {
	/** @var string */
	private $root;
	/** @var string */
	private $webroot;

	public function __construct(string $root, string $webroot) {
		$this->root = $root;
		$this->webroot = $webroot;
	}

	public function store(string $sourcePath, string $name): string {
		rename($sourcePath, $this->generatePath($name));
		return $this->getUrl($name);
	}

	private function generatePath(string $name): string {
		return $this->root . '/' . substr($name, 0, 2) . '/' . substr($name, 2, 4) . '/' . $name;
	}

	private function getUrl(string $name): string {
		return 'https://' . $this->webroot . '/' . substr($name, 0, 2) . '/' . substr($name, 2, 4) . '/' . $name;
	}
}
