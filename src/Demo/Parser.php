<?php namespace Demostf\API\Demo;

use GuzzleHttp\Client;

class Parser {
	const ANALYSER_BASEURL = 'http://demoserver.azurewebsites.net';

	/**
	 * @param string $head string containing the demo header binary data
	 * @return Header
	 * @throws \Exception
	 */
	public function parseString($head) {
		set_error_handler(array($this, 'errorHandler'));
		$info = unpack("A8type/Iversion/Iprotocol/A260server/A260nick/A260map/A260game/fduration/Iticks/Iframes/Isigon",
			$head);
		restore_error_handler();
		if ($info['type'] !== 'HL2DEMO') {
			throw new \Exception('Not an HL2 demo');
		}
		return new Header($info);
	}

	/**
	 * Parse demo info from a stream
	 *
	 * @param resource $stream
	 * @return Header
	 * @throws \Exception
	 */
	public function parseStream($stream) {
		$head = fread($stream, 2048);
		return $this->parseString($head);
	}

	/**
	 * Parse demo info from a local file
	 *
	 * @param string $path
	 * @return Header
	 * @throws \Exception
	 */
	public function parseHeader($path) {
		if (!is_readable($path)) {
			throw new \Exception('Unable to open demo: ' . $path);
		}
		$fh = fopen($path, 'rb');
		return $this->parseStream($fh);
	}

	public function analyse(StoredDemo $storedDemo) {
		$endPoint = self::ANALYSER_BASEURL . '/url';
		$client = new Client();
		$response = $client->post($endPoint, [
			'body' => $storedDemo->getUrl()
		]);
		$data = $response->getBody();
		return $this->handleData($data);
	}

	private function handleData($data) {
		if (!is_array($data)) {
			throw new \Exception('Error parsing demo');
		}
		$intervalPerTick = $data['intervalPerTick'];
		$red = 0;
		$blue = 0;
		$chat = [];
		$players = [];
		foreach ($data['rounds'] as $round) {
			if ($round['winner'] === 'red') {
				$red++;
			} else {
				$blue++;
			}
		}

		foreach ($data['chat'] as $message) {
			if (isset($message['from'])) {
				$chat[] = [
					'time' => floor(($message['tick'] - $data['startTick']) * $intervalPerTick),
					'from' => $message['from'],
					'text' => $message['text']
				];
			}
		}

		foreach ($data['users'] as $player) {
			$class = 0;
			$classSpawns = 0;
			foreach ($player['classes'] as $classId => $spawns) {
				if ($spawns > $classSpawns) {
					$classSpawns = $spawns;
					$class = $classId;
				}
			}
			if ($class and $player['steamId']) {//skip spectators
				$players[] = [
					'name' => $player['name'],
					'demo_user_id' => $player['userId'],
					'steam_id' => $player['steamId'],
					'team' => $player['team'],
					'class' => $this->getClassName($class)
				];
			}
		}

		return [
			'score' => [
				'red' => $red,
				'blue' => $blue
			],
			'chat' => $chat,
			'players' => $players,
			'kills' => $data['deaths']
		];
	}

	private function getClassName($classId) {
		$classes = [
			1 => 'scout',
			2 => 'sniper',
			3 => 'soldier',
			4 => 'demoman',
			5 => 'medic',
			6 => 'heavyweapons',
			7 => 'pyro',
			8 => 'spy',
			9 => 'engineer'
		];
		return isset($classes[$classId]) ? $classes[$classId] : 'Unknown';
	}
}
