<?php declare(strict_types=1);

namespace Demostf\API\Demo;

/**
 * Higher level parser
 *
 * Processes the raw demo.js output to something more suitable for our purpose
 */
class Parser {
	const CLASSES = [
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

	/** @var RawParser */
	private $rawParser;

	public function __construct(RawParser $rawParser) {
		$this->rawParser = $rawParser;
	}

	public function analyse(string $path): array {
		$data = $this->rawParser->parse($path);
		if (!is_array($data)) {
			throw new \InvalidArgumentException('Error parsing demo');
		}
		return $this->handleData($data);
	}

	private function handleData(array $data) {
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
			if ($class && $player['steamId']) {//skip spectators
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

	private function getClassName(int $classId): string {
		return self::CLASSES[$classId] ?? 'Unknown';
	}
}
