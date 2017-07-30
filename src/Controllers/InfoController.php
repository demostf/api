<?php namespace Demostf\API\Controllers;

use Demostf\API\Providers\InfoProvider;
use flight\Engine;

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
