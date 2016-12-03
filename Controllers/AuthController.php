<?php namespace Controllers;

use Ehesp\SteamLogin\SteamLogin;
use Providers\AuthProvider;
use Providers\UserProvider;

class AuthController extends BaseController {
	/**
	 * @var UserProvider
	 */
	private $userProvider;

	/**
	 * @var AuthProvider
	 */
	private $authProvider;

	/**
	 * AuthController constructor.
	 *
	 * @param UserProvider $userProvider
	 * @param AuthProvider $authProvider
	 */
	public function __construct(UserProvider $userProvider, AuthProvider $authProvider) {
		$this->userProvider = $userProvider;
		$this->authProvider = $authProvider;
	}

	public function token() {
		echo $this->authProvider->generateToken();
	}

	public function get($token) {
		$userData = $this->authProvider->getUser($token);
		\Flight::json([
			'token' => $token,
			'steamid' => $userData['steamid'],
			'name' => $userData['name'],
			'key' => $userData['key']
		]);
	}

	public function login($token) {
		$_SESSION['return'] = $this->query('return', 'http://demos.tf');
		$steam = new SteamLogin();
		$url = $steam->url($_ENV['APP_ROOT'] . '/auth/handle/' . urlencode($token));
		\Flight::redirect(str_replace('&amp;', '&', $url)); // headers make no sense
	}

	public function logout($token) {
		$this->authProvider->logout($token);
		\Flight::json([
			'token' => $token,
			'steamid' => null,
			'name' => null,
			'key' => null
		]);
	}

	public function handle($token) {
		$return = isset($_SESSION['return']) ? $_SESSION['return'] : 'http://demos.tf';
		unset($_SESSION['return']);
		$steam = new SteamLogin();
		$steamId = $steam->validate();
		if ($steamId) {
			$steamIdObject = new \SteamId($steamId);
			$key = $this->userProvider->store($steamIdObject);
			$this->authProvider->setUser($token, $steamIdObject, $key);
		}
		\Flight::redirect($return);
	}
}
