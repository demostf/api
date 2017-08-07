<?php

declare(strict_types=1);

namespace Demostf\API\Controllers;

use Demostf\API\Providers\InfoProvider;
use flight\net\Request;
use flight\net\Response;

class InfoController extends BaseController {
    /** @var InfoProvider */
    private $infoProvider;

    public function __construct(Request $request, Response $response, InfoProvider $infoProvider) {
        parent::__construct($request, $response);
        $this->infoProvider = $infoProvider;
    }

    public function listMaps() {
        \Flight::json($this->infoProvider->listMaps());
    }

    public function stats() {
        \Flight::json($this->infoProvider->getStats());
    }
}
