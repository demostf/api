<?php namespace Demostf\API\Demo;

use Demostf\API\Data\StoredDemo;

class DemoStore {
	/** @var string */
	private $root;
	/** @var string */
	private $webroot;

	public function __construct(string $root, string $webroot) {
		$this->root = $root;
		$this->webroot = $webroot;
	}

	public function store(string $sourcePath, string $name): StoredDemo {
		$target = $this->generatePath($name);
		if (!is_dir(dirname($target))) {
			mkdir(dirname($target), 0777, true);
		}
		rename($sourcePath, $target);
		chmod($target, 0755);
		return new StoredDemo($this->getUrl($name), 'static', $target);
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
