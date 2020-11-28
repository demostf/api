<?php

declare(strict_types=1);

namespace Demostf\API\Providers;

use Demostf\API\Data\SteamUser;
use Demostf\API\Data\User;
use Doctrine\DBAL\Connection;
use PDO;
use RandomLib\Generator;
use SteamId;

class UserProvider extends BaseProvider {
    /**
     * @var Generator
     */
    private $generator;

    public function __construct(Connection $db, Generator $generator) {
        parent::__construct($db);
        $this->generator = $generator;
    }

    public function store(SteamId $steamId): string {
        $token = $this->generator->generateString(64, Generator::EASY_TO_READ);

        $user = $this->get($steamId->getSteamId64());
        if ($user) {
            return $user->getToken();
        }

        $query = $this->getQueryBuilder();
        $query->insert('users')
            ->values([
                'steamid' => $query->createNamedParameter($steamId->getSteamId64()),
                'name' => $query->createNamedParameter($steamId->getNickname()),
                'avatar' => $query->createNamedParameter($steamId->getMediumAvatarUrl()),
                'token' => $query->createNamedParameter($token),
            ])->add('orderBy', 'ON CONFLICT DO NOTHING')// hack to append arbitrary string to sql
            ->execute();

        $user = $this->get($steamId->getSteamId64());

        return $user ? $user->getToken() : $token;
    }

    public function get(string $steamid): ?User {
        // first search in the view which contains the most used name for the users

        $query = $this->getQueryBuilder();
        $query->select(['id', 'steamid', 'name', 'avatar', 'token'])
            ->from('users_named')
            ->where($query->expr()->eq('steamid', $query->createNamedParameter($steamid)));

        $row = $query->execute()->fetch();

        if (!$row) {
            // if the user is newly inserted it wont be in our view yet

            $query = $this->getQueryBuilder();
            $query->select(['id', 'steamid', 'name', 'avatar', 'token'])
                ->from('users')
                ->where($query->expr()->eq('steamid', $query->createNamedParameter($steamid)));

            $row = $query->execute()->fetch();
        }

        return $row ? User::fromRow($row) : null;
    }

    public function getById(int $userId): ?User {
        if ($userId > pow(2, 31)) {
            return null;
        }
        // first search in the view which contains the most used name for the users

        $query = $this->getQueryBuilder();
        $query->select(['id', 'steamid', 'name', 'avatar', 'token'])
            ->from('users_named')
            ->where($query->expr()->eq('id', $query->createNamedParameter($userId, \PDO::PARAM_INT)));

        $row = $query->execute()->fetch();

        if (!$row) {
            // if the user is newly inserted it wont be in our view yet

            $query = $this->getQueryBuilder();
            $query->select(['id', 'steamid', 'name', 'avatar', 'token'])
                ->from('users')
                ->where($query->expr()->eq('id', $query->createNamedParameter($userId, \PDO::PARAM_INT)));

            $row = $query->execute()->fetch();
        }

        return $row ? User::fromRow($row) : null;
    }

    private function searchBySteamId(string $steamId): ?array {
        $query = $this->getQueryBuilder();
        $query->select('u.id', 'p.name', 'count(demo_id) as count', 'steamid')
            ->from('players', 'p')
            ->innerJoin('p', 'users', 'u', $query->expr()->eq('p.user_id', 'u.id'))
            ->where($query->expr()->eq('steamid', $query->createNamedParameter($steamId)))
            ->groupBy('p.name, u.id')
            ->orderBy('count(demo_id)', 'DESC')
            ->setMaxResults(1);

        $result = $query->execute()->fetch();
        if (\is_array($result)) {
            return $result;
        } else {
            return null;
        }
    }

    /**
     * @return SteamUser[]
     */
    public function search(string $search): array {
        $bySteamId = $this->searchBySteamId($search);
        if ($bySteamId) {
            return [
                $bySteamId,
            ];
        }

        $query = $this->getQueryBuilder();
        $nameParameter = $query->createNamedParameter($search, PDO::PARAM_STR, ':query');
        $query->select('user_id', 'name', 'count', 'steamid', "1 - (name <-> $nameParameter) AS sim")
            ->from('name_list')
            ->orWhere($query->expr()->comparison('name', '~*', $nameParameter))
            ->orderBy('count', 'DESC')
            ->setMaxResults(100);
        $result = $query->execute();
        $players = $result->fetchAll(PDO::FETCH_ASSOC);

        usort($players, function ($b, $a) use ($query) {
            if ($a['steamid'] === $query && $a['steamid'] !== $query) {
                return -1;
            }
            $countWeight = 1;
            $simWeight = 5;
            $diff = ($a['sim'] * $simWeight + $a['count'] * $countWeight) - ($b['sim'] * $simWeight + $b['count'] * $countWeight);
            if (0 === $diff) {
                return 0;
            } else {
                return ($diff < 0) ? -1 : 1;
            }
        });

        $result = [];
        foreach ($players as $player) {
            $id = $player['user_id'];
            if (!isset($result[$id])) {
                $result[$id] = new SteamUser($id, $player['steamid'], $player['name']);
            }
        }

        return array_values($result);
    }

    public function byKey(string $key): ?User {
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

        $this->store(new SteamId($steamId));

        return $this->get($steamId)->getId();
    }
}
