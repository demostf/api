<?php namespace Controllers;

class BaseController {
	protected function query($name, $default) {
		$request = \Flight::request();
		return isset($request->query[$name]) ? $request->query[$name] : $default;
	}
}
