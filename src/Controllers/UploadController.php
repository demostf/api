<?php namespace Demostf\API\Controllers;

use Demostf\API\Demo\DemoStore;
use Demostf\API\Demo\Parser;
use Demostf\API\Providers\DemoProvider;
use Demostf\API\Providers\UserProvider;

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

	/** @var DemoStore */
	private $store;

	public function __construct(DemoProvider $demoProvider, UserProvider $userProvider, Parser $parser, DemoStore $store) {
		$this->demoProvider = $demoProvider;
		$this->userProvider = $userProvider;
		$this->parser = $parser;
		$this->store = $store;
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
		$tmpPath = $demo['tmp_name'];
		try {
			$info = $this->parser->parseHeader($tmpPath);
		} catch (\Exception $e) {
			return 'Not a valid demo';
		}

		if ($info->getDuration() < (5 * 60)) {
			return 'Demos need to be at least 5m long';
		}

		if ($info->getDuration() > (60 * 60)) {
			return 'Demos cant be longer than one hour';
		}

		$hash = hash_file('md5', $tmpPath);

		$existingDemo = $this->demoProvider->demoIdByHash($hash);
		if ($existingDemo) {
			if ($key) {
				return 'STV available at: https://demos.tf/' . $existingDemo;
			} else {
				\Flight::redirect('https://demos.tf/' . $existingDemo);
				return '';
			}
		}

		$url = $this->store->store($tmpPath, $hash . '_' . $name);
	}
}
