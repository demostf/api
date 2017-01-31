<?php namespace Controllers;

use Demo\Parser;
use Providers\DemoProvider;
use Providers\UserProvider;

class UploadController extends BaseController {

	/**
	 * @var \Providers\DemoProvider
	 */
	private $demoProvider;

	/**
	 * @var UserProvider
	 */
	private $userProvider;

	/**
	 * @var Parser
	 */
	private $parser;

	public function __construct(DemoProvider $demoProvider, UserProvider $userProvider, Parser $parser) {
		$this->demoProvider = $demoProvider;
		$this->userProvider = $userProvider;
		$this->parser = $parser;
	}

	public function upload() {
		$key = $this->post('key');
		$red = $this->post('red', 'RED');
		$blu = $this->post('blu', 'BLU');
		$name = $this->post('name', 'Unnamed');
		$demo = $this->file('demo');
		$user = $this->userProvider->byKey($key);
		if (!$user) {
			return 'Invalid key';
		}
		if (!$red) {
			$red = 'RED';
		}
		if (!$blu) {
			$blu = 'BLU';
		}

		$size = $demo['size'];
		if ($size < 1024) {
			return 'Demos needs to be at least 1KB is size';
		}

		if ($size > 100 * 1024 * 1024) {
			return 'Demos cant be more than 100MB in size';
		}
		try {
			$info = $this->parser->parseFile($demo['tmp_name']);
		} catch (\Exception $e) {
			return 'Not a valid demo';
		}

		if ($info->getDuration() < (5 * 60)) {
			return 'Demos need to be at least 5m long';
		}

		if ($info->getDuration() > (60 * 60)) {
			return 'Demos cant be longer than one hour';
		}

		$tmpPath = $demo->getPathname();
		$hash = hash_file('md5', $demo['tmp_name']);

		$existingDemo = $this->demoProvider->demoIdByHash($hash);
		if ($existingDemo) {
			if ($key) {
				return 'STV available at: https://demos.tf/' . $existingDemo;
			} else {
				\Flight::redirect('https://demos.tf/' . $existingDemo);
				return '';
			}
		}

		$handle = fopen($tmpPath, 'rb');
		$storedDemo = $this->demoProvider->storeDemo($handle, $name);
	}
}
