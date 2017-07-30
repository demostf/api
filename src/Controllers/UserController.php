<?php namespace Demostf\API\Controllers;

use Ehesp\SteamLogin\SteamLogin;
use Demostf\API\Providers\AuthProvider;
use Demostf\API\Providers\UserProvider;
use flight\Engine;

class UserController extends BaseController {
    /**
     * @var UserProvider
     */
    private $userProvider;

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
