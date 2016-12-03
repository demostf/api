<?php namespace Controllers;

use Ehesp\SteamLogin\SteamLogin;
use Providers\AuthProvider;
use Providers\UserProvider;

class UserController extends BaseController {
	/**
	 * @var UserProvider
	 */
	private $userProvider;

	/**
	 * UserController constructor.
	 *
	 * @param UserProvider $userProvider
	 */
	public function __construct(UserProvider $userProvider) {
		$this->userProvider = $userProvider;
	}

	public function get($steamid) {
		\Flight::json($this->userProvider->get($steamid));
	}

	public function search() {
		$query = $this->query('query', '');
		\Flight::json($this->userProvider->search($query));
	}
}
