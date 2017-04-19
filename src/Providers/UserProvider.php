<?php declare(strict_types=1);

namespace Demostf\API\Providers;

use Demostf\API\Data\User;
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

	public function store(\SteamId $steamId): string {
		$token = $this->generator->generateString(64, Generator::EASY_TO_READ);

		$query = $this->getQueryBuilder();
		$query->insert('users')
			->values([
				'steamid' => $query->createNamedParameter($steamId->getSteamId64()),
				'name' => $query->createNamedParameter($steamId->getNickname()),
				'avatar' => $query->createNamedParameter($steamId->getMediumAvatarUrl()),
				'token' => $query->createNamedParameter($token)
			])->add('orderBy', 'ON CONFLICT DO NOTHING')// hack to append arbitrary string to sql
			->execute();

		$user = $this->get($steamId->getSteamId64());
		return $user ? $user->getToken() : $token;
	}

	public function get(string $steamid): ?User {
		$query = $this->getQueryBuilder();
		$query->select(['id', 'steamid', 'name', 'avatar', 'token'])
			->from('users')
			->where($query->expr()->eq('steamid', $query->createNamedParameter($steamid)));

		$row = $query->execute()->fetch();
		return $row ? User::fromRow($row) : null;
	}

	public function search($query): array {
		$sql = 'SELECT user_id, players.name, count(demo_id) AS count, steamid,
		 1-(players.name <-> ?) AS sim FROM players
		 INNER JOIN users ON users.id = players.user_id
		 WHERE players.name % ? OR players.name ~* ? OR steamid = ?
		 GROUP BY players.name, user_id, steamid
		 ORDER BY count DESC
		 LIMIT 100';
		$result = $this->query($sql, [$query, $query, $query, $query]);
		$players = $result->fetchAll(\PDO::FETCH_ASSOC);

		usort($players, function ($b, $a) use ($query) {
			if ($a['steamid'] === $query && $a['steamid'] !== $query) {
				return -1;
			}
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

		return array_values($result);
	}

	public function byKey($key): ?User {
		$query = $this->getQueryBuilder();
		$query->select(['id', 'steamid', 'name', 'avatar', 'token'])
			->from('users')
			->where($query->expr()->eq('token', $query->createNamedParameter($key)));

		$row = $query->execute()->fetch();
		return $row ? User::fromRow($row) : null;
	}

	public function getUserId(string $steamId) {
		$existing = $this->get($steamId);
		if ($existing) {
			return $existing->getId();
		}

		$this->store(new \SteamId($steamId));

		return $this->get($steamId)->getId();
	}
}
