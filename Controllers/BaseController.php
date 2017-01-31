<?php namespace Controllers;

class BaseController {
	protected function query($name, $default) {
		$request = \Flight::request();
		return isset($request->query[$name]) ? $request->query[$name] : $default;
	}

	protected function file($name) {
		$request = \Flight::request();
		return $request->files[$name];
	}

	protected function post($name, $default = null) {
		$request = \Flight::request();
		return isset($request->data[$name]) ? $request->data[$name] : $default;
	}
}
