<?php

declare(strict_types=1);

namespace Demostf\API\Controllers;

use Demostf\API\Providers\InfoProvider;

class InfoController extends BaseController {
    /** @var InfoProvider */
    private $infoProvider;

    public function __construct(InfoProvider $infoProvider) {
        $this->infoProvider = $infoProvider;
    }

    public function listMaps() {
        \Flight::json($this->infoProvider->listMaps());
    }

    public function stats() {
        \Flight::json($this->infoProvider->getStats());
    }
}
