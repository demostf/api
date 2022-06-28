<?php

declare(strict_types=1);

namespace Demostf\API\Controllers;

use Demostf\API\Demo\DemoStore;
use Demostf\API\Error\InvalidHashException;
use Demostf\API\Error\InvalidKeyException;
use Demostf\API\Error\NotFoundException;
use Demostf\API\Providers\ChatProvider;
use Demostf\API\Providers\DemoListProvider;
use Demostf\API\Providers\DemoProvider;
use flight\net\Request;
use flight\net\Response;

class DemoController extends BaseController {
    private DemoProvider $demoProvider;
    private ChatProvider $chatProvider;
    private DemoListProvider $demoListProvider;
    private string $editKey;
    private DemoStore $store;

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

    public function get(string $id): void {
        $demo = $this->demoProvider->get(\intval($id, 10));
        if (null === $demo) {
            throw new NotFoundException('requested demo not found');
        } else {
            $this->json($demo);
        }
    }

    /**
     * @return array<mixed>
     */
    protected function getFilter(): array {
        $map = $this->query('map', '');
        $players = $this->query('players', '');
        $type = $this->query('type', '');
        $backend = $this->query('backend', '');
        $before = $this->query('before', '');
        $after = $this->query('after', '');
        $afterId = $this->query('after_id', '');
        $beforeId = $this->query('before_id', '');
        $filter = [];
        if ($map) {
            $filter['map'] = $map;
        }
        if ($backend) {
            $filter['backend'] = $backend;
        }
        if ($players) {
            if (!\is_array($players)) {
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
        if ($before) {
            $date = \DateTime::createFromFormat('U', $before);
            if ($date) {
                $filter['before'] = $date;
            }
        }
        if ($after) {
            $date = \DateTime::createFromFormat('U', $after);
            if ($date) {
                $filter['after'] = $date;
            }
        }
        if ($afterId) {
            $filter['after_id'] = $afterId;
        }
        if ($beforeId) {
            $filter['before_id'] = $beforeId;
        }

        return $filter;
    }

    public function listDemos(): void {
        $page = (int) $this->query('page', '1');
        $order = 'ASC' === $this->query('order', 'DESC') ? 'ASC' : 'DESC';
        $this->json($this->demoListProvider->listDemos((int) $page, $this->getFilter(), $order));
    }

    public function listProfile(string $steamId): void {
        $page = (int) $this->query('page', '1');
        $where = $this->getFilter();
        $where['players'][] = $steamId;
        $order = 'ASC' === $this->query('order', 'DESC') ? 'ASC' : 'DESC';
        $this->json($this->demoListProvider->listProfile((int) $page, $where, $order));
    }

    public function listUploads(string $steamId): void {
        $page = (int) $this->query('page', '1');
        $order = 'ASC' === $this->query('order', 'DESC') ? 'ASC' : 'DESC';
        $this->json($this->demoListProvider->listUploads($steamId, (int) $page, $this->getFilter(), $order));
    }

    public function chat(string $demoId): void {
        $this->json($this->chatProvider->getChat((int) $demoId));
    }

    public function setDemoUrl(string $id): void {
        $hash = $this->post('hash', '');
        $backend = $this->post('backend', '');
        $path = $this->post('path', '');
        $url = $this->post('url', '');
        $editKey = $this->post('key', '');
        if ($editKey !== $this->editKey || '' === $editKey) {
            throw new InvalidKeyException('Invalid key');
        }

        $demo = $this->demoProvider->get((int) $id);
        if (!$demo) {
            throw new NotFoundException('Demo not found');
        }

        $existingHash = $demo->getHash();
        if ('' === $existingHash || $existingHash === $hash) {
            $this->demoProvider->setDemoUrl((int) $id, $backend, $url, $path);

            if ('static' === $demo->getBackend()) {
                $this->store->remove($demo);
            }
        } else {
            throw new InvalidHashException('Invalid demo hash');
        }
    }
}
