<?php

declare(strict_types=1);

namespace Demostf\API\Controllers;

use Demostf\API\Providers\InfoProvider;
use Flight;
use flight\net\Request;
use flight\net\Response;

class InfoController extends BaseController {
    private InfoProvider $infoProvider;

    public function __construct(Request $request, Response $response, InfoProvider $infoProvider) {
        parent::__construct($request, $response);
        $this->infoProvider = $infoProvider;
    }

    public function listMaps(): void {
        Flight::json($this->infoProvider->listMaps());
    }

    public function stats(): void {
        Flight::json($this->infoProvider->getStats());
    }
}
