<?php namespace Demostf\API\Controllers;

use Ehesp\SteamLogin\SteamLogin;
use Demostf\API\Providers\AuthProvider;
use Demostf\API\Providers\UserProvider;
use flight\Engine;

class AuthController extends BaseController {
    /**
     * @var UserProvider
     */
    private $userProvider;

    /**
     * @var AuthProvider
     */
    private $authProvider;

    /** @var string */
    private $host;

    private $apiRoot;

    public function __construct(UserProvider $userProvider, AuthProvider $authProvider, string $host, string $apiRoot) {
        $this->userProvider = $userProvider;
        $this->authProvider = $authProvider;
        $this->host = $host;
        $this->apiRoot = $apiRoot;
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
        $_SESSION['return'] = $this->query('return', 'https://' . $this->host);
        $steam = new SteamLogin();
        $url = $steam->url($this->apiRoot . '/auth/handle/' . urlencode($token), $this->apiRoot);
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
        $return = $_SESSION['return'] ?? 'https://' . $this->host;
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
