<?php namespace Demostf\API\Controllers;

use Demostf\API\Providers\UploadProvider;
use flight\Engine;

class UploadController extends BaseController {
	private $uploadProvider;

	public function __construct(UploadProvider $uploadProvider) {
		$this->uploadProvider = $uploadProvider;
	}

	public function upload() {
		$key = $this->post('key', '');
		$red = $this->post('red', 'RED');
		$blu = $this->post('blu', 'BLU');
		$name = $this->post('name', 'Unnamed');
		$demo = $this->file('demo');
		$demoFile = $demo['tmp_name'];

		echo $this->uploadProvider->upload($key, $red, $blu, $name, $demoFile);
	}
}
