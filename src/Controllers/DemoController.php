<?php namespace Demostf\API\Controllers;

use Demostf\API\Providers\ChatProvider;
use Demostf\API\Providers\DemoListProvider;
use Demostf\API\Providers\DemoProvider;
use flight\Engine;

class DemoController extends BaseController {
	/** @var DemoProvider */
	private $demoProvider;

	/** @var ChatProvider */
	private $chatProvider;

	private $demoListProvider;

	private $editKey;

	public function __construct(DemoProvider $demoProvider, ChatProvider $chatProvider, DemoListProvider $demoListProvider, string $editKey) {
		$this->demoProvider = $demoProvider;
		$this->chatProvider = $chatProvider;
		$this->demoListProvider = $demoListProvider;
		$this->editKey = $editKey;
	}

	/**
	 * @param  string $id
	 */
	public function get($id) {
		\Flight::json($this->demoProvider->get($id));
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
		\Flight::json($this->demoListProvider->listDemos($page, $this->getFilter(), $order));
	}

	public function listProfile($steamid) {
		$page = $this->query('page', 1);
		$where = $this->getFilter();
		$where['players'][] = $steamid;
		\Flight::json($this->demoListProvider->listProfile($page, $where));
	}

	public function listUploads($steamid) {
		$page = $this->query('page', 1);
		\Flight::json($this->demoListProvider->listUploads($steamid, $page, $this->getFilter()));
	}

	public function chat($demoId) {
		\Flight::json($this->chatProvider->getChat($demoId));
	}

	public function setDemoUrl($id) {
		$hash = $this->query('hash', '');
		$backend = $this->query('backend', '');
		$path = $this->query('path', '');
		$url = $this->query('url', '');
		$editKey = $this->query('key', '');
		if ($editKey !== $this->editKey) {
			throw new \InvalidArgumentException('Invalid key');
		}

		$demo = $this->demoProvider->get($id);
		$existingHash = $demo->getHash();
		if ($existingHash === '' || $existingHash === $hash) {
			$this->demoProvider->setDemoUrl($id, $backend, $url, $path);
		} else {
			throw new \InvalidArgumentException('Invalid demo hash');
		}
	}
}
