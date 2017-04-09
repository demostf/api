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
		$target = $this->generatePath($name);
		if (!is_dir(dirname($target))) {
			mkdir(dirname($target), 0777, true);
		}
		rename($sourcePath, $target);
		return $this->getUrl($name);
	}

	private function generatePath(string $name): string {
		return $this->root . $this->getPrefix($name) . $name;
	}

	private function getPrefix(string $name) {
		return '/' . substr($name, 0, 2) . '/' . substr($name, 2, 2) . '/';
	}

	private function getUrl(string $name): string {
		return 'https://' . $this->webroot . $this->getPrefix($name) . $name;
	}
}
