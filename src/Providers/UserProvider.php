<?php namespace Demostf\API\Providers;

use Doctrine\DBAL\Connection;
use RandomLib\Generator;

class UserProvider extends BaseProvider {
	/**
	 * @var Generator
	 */
	private $generator;

	public function __construct(Connection $db, Generator $generator) {
		parent::__construct($db);
		$this->generator = $generator;
	}

	public function store(\SteamId $steamId) {
		$sql = 'INSERT INTO users(steamid, name, avatar, token)
		SELECT ?, ?, ?, ? WHERE NOT EXISTS(SELECT id FROM users WHERE steamid = ?)';
		$this->query($sql, [
			$steamId->getSteamId64(),
			$steamId->getNickname(),
			$steamId->getMediumAvatarUrl(),
			$this->generator->generateString(64),
			$steamId->getSteamId64()
		]);

		$user = $this->db->user()->where('steamid', $steamId->getSteamId64());
		return $user->fetch()->token;
	}

	public function get($steamid) {
		$query = $this->getQueryBuilder();
		$query->select(['id', 'steamid', 'name', 'avatar', 'token'])
			->from('users')
			->where($query->expr()->eq('steamid', $query->createNamedParameter($steamid)));

		return $query->execute()->fetch();
	}

	public function search($query) {
		$sql = 'SELECT user_id, players.name, count(demo_id) AS count, steamid,
		 1-(players.name <-> ?) AS sim FROM players
		 INNER JOIN users ON users.id = players.user_id
		 WHERE players.name % ? OR players.name ~* ?
		 GROUP BY players.name, user_id, steamid
		 ORDER BY count DESC
		 LIMIT 100';
		$result = $this->query($sql, [$query, $query, $query]);
		$players = $result->fetchAll(\PDO::FETCH_ASSOC);

		usort($players, function ($b, $a) {
			$countWeight = 1;
			$simWeight = 5;
			$diff = ($a['sim'] * $simWeight + $a['count'] * $countWeight) - ($b['sim'] * $simWeight + $b['count'] * $countWeight);
			if ($diff === 0) {
				return 0;
			} else {
				return ($diff < 0) ? -1 : 1;
			}
		});

		$result = [];
		foreach ($players as $player) {
			$id = $player['user_id'];
			if (!isset($result[$id])) {
				$result[$id] = [
					'id' => $id,
					'name' => $player['name'],
					'steamid' => $player['steamid']
				];
			}
		}

		$players = array_values($result);

		return $players;
	}

	public function byKey($key) {
		$query = $this->getQueryBuilder();
		$query->select(['id', 'steamid', 'name', 'avatar'])
			->from('users')
			->where($query->expr()->eq('token', $query->createNamedParameter($key)));

		return $query->execute()->fetch();
	}
}
