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
		if (is_null($demo)) {
			echo 'No demo uploaded';
			return;
		}
		$demoFile = $demo['tmp_name'];

		try {
			$result = $this->uploadProvider->upload($key, $red, $blu, $name, $demoFile);
			if ($result === 'Invalid key') {
				\Flight::response()->status(401)->write($result)->send();
			} else {
				echo $result;
			}
		} catch (\Exception $e) {
			\Flight::response()
				->status(500)
				->write($e->getMessage())
				->send();
		}
	}
}
