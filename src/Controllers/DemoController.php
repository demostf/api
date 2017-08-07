<?php

declare(strict_types=1);

namespace Demostf\API\Controllers;

use Demostf\API\Providers\ChatProvider;
use Demostf\API\Providers\DemoListProvider;
use Demostf\API\Providers\DemoProvider;

class DemoController extends BaseController {
    /** @var DemoProvider */
    private $demoProvider;

    /** @var ChatProvider */
    private $chatProvider;

    private $demoListProvider;

    private $editKey;

    public function __construct(
        DemoProvider $demoProvider,
        ChatProvider $chatProvider,
        DemoListProvider $demoListProvider,
        string $editKey
    ) {
        $this->demoProvider = $demoProvider;
        $this->chatProvider = $chatProvider;
        $this->demoListProvider = $demoListProvider;
        $this->editKey = $editKey;
    }

    /**
     * @param string $id
     */
    public function get($id) {
        \Flight::json($this->demoProvider->get(intval($id, 10)));
    }

    protected function getFilter() {
        $map = $this->query('map', '');
        $players = $this->query('players', '');
        $type = $this->query('type', '');
        $filter = [];
        if ($map) {
            $filter['map'] = $map;
        }
        if ($players) {
            if (!is_array($players)) {
                $players = explode(',', $players);
            }
            $players = array_filter($players);
            $filter['players'] = $players;
        }
        switch ($type) {
            case 'hl':
                $filter['playerCount'] = [17, 18, 19];
                break;
            case '6v6':
                $filter['playerCount'] = [11, 12, 13];
                break;
            case '4v4':
                $filter['playerCount'] = [7, 8, 9];
                break;
        }

        return $filter;
    }

    public function listDemos() {
        $page = $this->query('page', 1);
        $order = $this->query('order', 'DESC') === 'ASC' ? 'ASC' : 'DESC';
        \Flight::json($this->demoListProvider->listDemos((int)$page, $this->getFilter(), $order));
    }

    public function listProfile($steamId) {
        $page = $this->query('page', 1);
        $where = $this->getFilter();
        $where['players'][] = $steamId;
        \Flight::json($this->demoListProvider->listProfile((int)$page, $where));
    }

    public function listUploads($steamId) {
        $page = $this->query('page', 1);
        \Flight::json($this->demoListProvider->listUploads($steamId, (int)$page, $this->getFilter()));
    }

    public function chat($demoId) {
        \Flight::json($this->chatProvider->getChat((int)$demoId));
    }

    public function setDemoUrl($id) {
        $hash = (string)$this->post('hash', '');
        $backend = (string)$this->post('backend', '');
        $path = (string)$this->post('path', '');
        $url = (string)$this->post('url', '');
        $editKey = (string)$this->post('key', '');
        if ($editKey !== $this->editKey || $editKey === '') {
            throw new \InvalidArgumentException('Invalid key');
        }

        $demo = $this->demoProvider->get((int)$id);
        $existingHash = $demo->getHash();
        if ($existingHash === '' || $existingHash === $hash) {
            $this->demoProvider->setDemoUrl((int)$id, $backend, $url, $path);
        } else {
            throw new \InvalidArgumentException('Invalid demo hash');
        }
    }
}
