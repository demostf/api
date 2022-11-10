<?php

declare(strict_types=1);

namespace Demostf\API\Providers;

use Demostf\API\Data\SteamUser;
use Demostf\API\Data\User;
use Doctrine\DBAL\Connection;
use PDO;
use RandomLib\Generator;
use SteamCondenser\Community\SteamId;

class UserProvider extends BaseProvider {
    /**
     * @var Generator
     */
    private $generator;

    public function __construct(Connection $db, Generator $generator) {
        parent::__construct($db);
        $this->generator = $generator;
    }

    public function store(SteamId $steamId, string $name): string {
        $token = $this->generator->generateString(64, Generator::EASY_TO_READ);

        $user = $this->get($steamId->getSteamId64());
        if ($user) {
            return $user->getToken();
        }

        $query = $this->getQueryBuilder();
        $query->insert('users')
            ->values([
                'steamid' => $query->createNamedParameter($steamId->getSteamId64()),
                'name' => $query->createNamedParameter($name),
                'avatar' => $query->createNamedParameter(''),
                'token' => $query->createNamedParameter($token),
            ])->add('orderBy', 'ON CONFLICT DO NOTHING')// hack to append arbitrary string to sql
            ->executeStatement();

        $user = $this->get($steamId->getSteamId64());

        return $user ? $user->getToken() : $token;
    }

    public function get(string $steamid): ?User {
        // first search in the view which contains the most used name for the users

        $query = $this->getQueryBuilder();
        $query->select(['id', 'steamid', 'name', 'token'])
            ->from('users_named')
            ->where($query->expr()->eq('steamid', $query->createNamedParameter($steamid)));

        $row = $query->executeQuery()->fetchAssociative();

        if (!$row) {
            // if the user is newly inserted it wont be in our view yet

            $query = $this->getQueryBuilder();
            $query->select(['id', 'steamid', 'name', 'token'])
                ->from('users')
                ->where($query->expr()->eq('steamid', $query->createNamedParameter($steamid)));

            $row = $query->executeQuery()->fetchAssociative();
        }

        return $row ? User::fromRow($row) : null;
    }

    public function getById(int $userId): ?User {
        if ($userId > 2 ** 31) {
            return null;
        }
        // first search in the view which contains the most used name for the users

        $query = $this->getQueryBuilder();
        $query->select(['id', 'steamid', 'name', 'token'])
            ->from('users_named')
            ->where($query->expr()->eq('id', $query->createNamedParameter($userId, \PDO::PARAM_INT)));

        $row = $query->executeQuery()->fetchAssociative();

        if (!$row) {
            // if the user is newly inserted it wont be in our view yet

            $query = $this->getQueryBuilder();
            $query->select(['id', 'steamid', 'name', 'token'])
                ->from('users')
                ->where($query->expr()->eq('id', $query->createNamedParameter($userId, \PDO::PARAM_INT)));

            $row = $query->executeQuery()->fetchAssociative();
        }

        return $row ? User::fromRow($row) : null;
    }

    private function searchBySteamId(string $steamId): ?SteamUser {
        $query = $this->getQueryBuilder();
        $query->select('u.id', 'p.name', 'count(demo_id) as count', 'steamid')
            ->from('players', 'p')
            ->innerJoin('p', 'users', 'u', $query->expr()->eq('p.user_id', 'u.id'))
            ->where($query->expr()->eq('steamid', $query->createNamedParameter($steamId)))
            ->groupBy('p.name, u.id')
            ->orderBy('count(demo_id)', 'DESC')
            ->setMaxResults(1);

        $row = $query->executeQuery()->fetchAssociative();
        if ($row) {
            return new SteamUser($row['id'], $row['steamid'], $row['name']);
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
        $query->select('n.user_id', 'n.name', 'count', 'n.steamid', "1 - (n.name <-> $nameParameter) AS sim", "u.name AS name_canonical")
            ->from('name_list', 'n')
            ->leftJoin('n', 'users_named', 'u', $query->expr()->eq('n.steamid', 'u.steamid'))
            ->orWhere($query->expr()->comparison('n.name', '~*', $nameParameter))
            ->orderBy('count', 'DESC')
            ->setMaxResults(100);
        $result = $query->executeQuery();
        $players = $result->fetchAllAssociative();

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
                if ($player['name_canonical'] && $player['name'] != $player['name_canonical']) {
                    $result[$id] = new SteamUser($id, $player['steamid'], $player['name'] . ' (' . $player['name_canonical'] . ')');
                } else {
                    $result[$id] = new SteamUser($id, $player['steamid'], $player['name']);
                }
            }
        }

        return array_values($result);
    }

    public function byKey(string $key): ?User {
        $query = $this->getQueryBuilder();
        $query->select(['id', 'steamid', 'name', 'token'])
            ->from('users')
            ->where($query->expr()->eq('token', $query->createNamedParameter($key)));

        $row = $query->executeQuery()->fetchAssociative();

        return $row ? User::fromRow($row) : null;
    }

    public function getUserId(string $steamId, string $name): int {
        $existing = $this->get($steamId);
        if ($existing) {
            return $existing->getId();
        }

        $this->store(new SteamId($steamId), $name);

        return $this->get($steamId)->getId();
    }
}
