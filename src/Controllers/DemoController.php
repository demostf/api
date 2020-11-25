<?php

declare(strict_types=1);

namespace Demostf\API\Controllers;

use Demostf\API\Demo\DemoStore;
use Demostf\API\Providers\ChatProvider;
use Demostf\API\Providers\DemoListProvider;
use Demostf\API\Providers\DemoProvider;
use flight\net\Request;
use flight\net\Response;
use function intval;
use InvalidArgumentException;
use function is_array;

class DemoController extends BaseController {
    /** @var DemoProvider */
    private $demoProvider;

    /** @var ChatProvider */
    private $chatProvider;

    private $demoListProvider;

    private $editKey;

    private $store;

    public function __construct(
        Request $request,
        Response $response,
        DemoProvider $demoProvider,
        ChatProvider $chatProvider,
        DemoListProvider $demoListProvider,
        DemoStore $store,
        string $editKey
    ) {
        parent::__construct($request, $response);
        $this->demoProvider = $demoProvider;
        $this->chatProvider = $chatProvider;
        $this->demoListProvider = $demoListProvider;
        $this->store = $store;
        $this->editKey = $editKey;
    }

    /**
     * @param string $id
     */
    public function get($id) {
        $this->json($this->demoProvider->get(intval($id, 10)));
    }

    protected function getFilter() {
        $map = $this->query('map', '');
        $players = $this->query('players', '');
        $type = $this->query('type', '');
        $backend = $this->query('backend', '');
        $filter = [];
        if ($map) {
            $filter['map'] = $map;
        }
        if ($backend) {
            $filter['backend'] = $backend;
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
            case 'prolander':
                $filter['playerCount'] = [14, 15];
                break;
            case '6v6':
                $filter['playerCount'] = [11, 12, 13];
                break;
            case '4v4':
                $filter['playerCount'] = [7, 8, 9];
                break;
            case '3v3':
                $filter['playerCount'] = [6];
                break;
            case '2v2':
                $filter['playerCount'] = [4];
                break;
            case '1v1':
                $filter['playerCount'] = [2];
                break;
        }

        return $filter;
    }

    public function listDemos() {
        $page = $this->query('page', 1);
        $order = 'ASC' === $this->query('order', 'DESC') ? 'ASC' : 'DESC';
        $this->json($this->demoListProvider->listDemos((int) $page, $this->getFilter(), $order));
    }

    public function listProfile($steamId) {
        $page = $this->query('page', 1);
        $where = $this->getFilter();
        $where['players'][] = $steamId;
        $this->json($this->demoListProvider->listProfile((int) $page, $where));
    }

    public function listUploads($steamId) {
        $page = $this->query('page', 1);
        $this->json($this->demoListProvider->listUploads($steamId, (int) $page, $this->getFilter()));
    }

    public function chat($demoId) {
        $this->json($this->chatProvider->getChat((int) $demoId));
    }

    public function setDemoUrl($id) {
        $hash = (string) $this->post('hash', '');
        $backend = (string) $this->post('backend', '');
        $path = (string) $this->post('path', '');
        $url = (string) $this->post('url', '');
        $editKey = (string) $this->post('key', '');
        if ($editKey !== $this->editKey || '' === $editKey) {
            throw new InvalidArgumentException('Invalid key');
        }

        $demo = $this->demoProvider->get((int) $id);
        $existingHash = $demo->getHash();
        if ('' === $existingHash || $existingHash === $hash) {
            $this->demoProvider->setDemoUrl((int) $id, $backend, $url, $path);

            if ('static' === $demo->getBackend()) {
                $this->store->remove($demo);
            }
        } else {
            throw new InvalidArgumentException('Invalid demo hash');
        }
    }
}
