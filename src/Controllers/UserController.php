<?php

declare(strict_types=1);

namespace Demostf\API\Controllers;

use Demostf\API\Providers\UserProvider;
use flight\net\Request;
use flight\net\Response;

class UserController extends BaseController {
    /**
     * @var UserProvider
     */
    private $userProvider;

    public function __construct(Request $request, Response $response, UserProvider $userProvider) {
        parent::__construct($request, $response);
        $this->userProvider = $userProvider;
    }

    public function get($steamId) {
        \Flight::json($this->userProvider->get($steamId));
    }

    public function search() {
        $query = $this->query('query', '');
        \Flight::json($this->userProvider->search($query));
    }
}
