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

	public function __construct(DemoProvider $demoProvider, ChatProvider $chatProvider, DemoListProvider $demoListProvider) {
		$this->demoProvider = $demoProvider;
		$this->chatProvider = $chatProvider;
		$this->demoListProvider = $demoListProvider;
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
		\Flight::json($this->demoListProvider->listDemos($page, $this->getFilter()));
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
}
