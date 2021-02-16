<?php

declare(strict_types=1);

namespace Demostf\API\Controllers;

use Demostf\API\Providers\AuthProvider;
use Demostf\API\Providers\UserProvider;
use Ehesp\SteamLogin\SteamLogin;
use Flight;
use flight\net\Request;
use flight\net\Response;
use SteamCondenser\Community\SteamId;

class AuthController extends BaseController {
    private UserProvider $userProvider;
    private AuthProvider $authProvider;
    private string $host;
    private string $apiRoot;

    public function __construct(
        Request $request,
        Response $response,
        UserProvider $userProvider,
        AuthProvider $authProvider,
        string $host,
        string $apiRoot
    ) {
        parent::__construct($request, $response);
        $this->userProvider = $userProvider;
        $this->authProvider = $authProvider;
        $this->host = $host;
        $this->apiRoot = $apiRoot;
    }

    public function token(): void {
        echo $this->authProvider->generateToken();
    }

    public function get(string $token): void {
        $userData = $this->authProvider->getUser($token);
        Flight::json([
            'token' => $token,
            'steamid' => $userData['steamid'],
            'name' => $userData['name'],
            'key' => $userData['key'],
        ]);
    }

    public function login(string $token): void {
        $_SESSION['return'] = $this->query('return', 'https://' . $this->host);
        $steam = new SteamLogin();
        $url = $steam->url($this->apiRoot . '/auth/handle/' . urlencode($token), $this->apiRoot);
        Flight::redirect(str_replace('&amp;', '&', $url)); // headers make no sense
    }

    public function logout(string $token): void {
        $this->authProvider->logout($token);
        Flight::json([
            'token' => $token,
            'steamid' => null,
            'name' => null,
            'key' => null,
        ]);
    }

    public function handle(string $token): void {
        $return = $_SESSION['return'] ?? 'https://' . $this->host;
        unset($_SESSION['return']);
        $steam = new SteamLogin();
        $steamId = $steam->validate();
        if ($steamId) {
            $steamIdObject = new SteamId($steamId);
            $key = $this->userProvider->store($steamIdObject, $steamIdObject->getNickname());
            $this->authProvider->setUser($token, $steamIdObject, $key);
        }
        Flight::redirect($return);
    }
}
