<?php

declare(strict_types=1);

namespace Demostf\API\Providers;

use Demostf\API\Demo\Demo;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use PDO;

class DemoListProvider extends BaseProvider {
    /**
     * @param mixed[] $where
     *
     * @throws \Doctrine\DBAL\Exception
     *
     * @return Demo[]
     */
    public function listUploads(string $steamId, int $page, array $where = [], string $order = 'DESC'): array {
        $query = $this->getQueryBuilder();
        $query->select('id')
            ->from('users')
            ->where($query->expr()->eq('steamid', $query->createNamedParameter($steamId, PDO::PARAM_STR)));

        $result = $query->executeQuery();
        $userId = $result->fetchOne();
        $result->free();

        $where['uploader'] = $userId;

        return $this->listDemos($page, $where, $order);
    }

    /**
     * @param mixed[] $where
     *
     * @throws \Doctrine\DBAL\Exception
     *
     * @return Demo[]
     */
    public function listProfile(int $page, array $where = [], string $order = 'DESC'): array {
        $players = $where['players'];
        $players = array_unique($players);
        unset($where['players']);

        $query = $this->getQueryBuilder();
        $query->select('id')
            ->from('users')
            ->where($query->expr()->in('steamid',
                $query->createNamedParameter($players, Connection::PARAM_STR_ARRAY)));
        $result = $query->executeQuery();
        $userIds = $result->fetchFirstColumn();
        $result->free();

        $query = $this->getQueryBuilder();
        $query->select('p.demo_id')
            ->from('players', 'p');

        if (\count($userIds) != \count($players)) {
            // one of more user ids don't have any demos
            return [];
        } elseif (\count($userIds) > 1) {
            $query->where($query->expr()->in('user_id',
                $query->createNamedParameter($userIds, Connection::PARAM_INT_ARRAY)))
                ->groupBy('demo_id')
                ->having($query->expr()->eq(
                    'COUNT(DISTINCT(user_id))',
                    $query->createNamedParameter(\count($userIds), PDO::PARAM_INT)
                ));
        } else {
            $query->where($query->expr()->eq('user_id',
                $query->createNamedParameter($userIds[0], PDO::PARAM_INT)));
        }
        $query->orderBy('demo_id', $order)
            ->setMaxResults(50)
            ->setFirstResult(((int) $page - 1) * 50);

        if (\count($where)) {
            $query->innerJoin('p', 'demos', 'd', $query->expr()->eq('demo_id', 'd.id'));
            $this->addWhere($query, $where);
        }

        $result = $query->executeQuery();
        $demoIds = $result->fetchFirstColumn();
        $result->free();

        $query = $this->getQueryBuilder();
        $query->select('*')
            ->from('demos')
            ->where($query->expr()->in('id', $query->createNamedParameter($demoIds, Connection::PARAM_INT_ARRAY)))
            ->orderBy('id', 'DESC');

        return $this->formatList($query->execute()->fetchAll());
    }

    /**
     * @param mixed[] $where
     */
    private function addWhere(QueryBuilder $query, array $where = []): void {
        if (isset($where['map'])) {
            $query->andWhere($query->expr()->orX(
                $query->expr()->eq('clean_map_name(map)', $query->createNamedParameter($where['map'])),
                $query->expr()->eq('map', $query->createNamedParameter($where['map']))
            ));
        }
        if (isset($where['playerCount'])) {
            $query->andWhere($query->expr()->in('"playerCount"',
                $query->createNamedParameter($where['playerCount'], Connection::PARAM_INT_ARRAY)));
        }
        if (isset($where['uploader'])) {
            $query->andWhere($query->expr()->in('uploader',
                $query->createNamedParameter($where['uploader'], PDO::PARAM_INT)));
        }
        if (isset($where['backend'])) {
            $query->andWhere($query->expr()->eq('backend',
                $query->createNamedParameter($where['backend'])));
        }
        if (isset($where['before'])) {
            $query->andWhere($query->expr()->lt('created_at',
                $query->createNamedParameter($where['before']->format(\DATE_ATOM))));
        }
        if (isset($where['after'])) {
            $query->andWhere($query->expr()->gt('created_at',
                $query->createNamedParameter($where['after']->format(\DATE_ATOM))));
        }
        if (isset($where['before_id'])) {
            $query->andWhere($query->expr()->lt('id',
                $query->createNamedParameter($where['before_id'], PDO::PARAM_INT)));
        }
        if (isset($where['after_id'])) {
            $query->andWhere($query->expr()->gt('id',
                $query->createNamedParameter($where['after_id'], PDO::PARAM_INT)));
        }
    }

    /**
     * @param mixed[] $where
     *
     * @throws \Doctrine\DBAL\Exception
     *
     * @return Demo[]
     */
    public function listDemos(int $page, array $where = [], string $order = 'DESC'): array {
        if (isset($where['players']) && \is_array($where['players']) && \count($where['players']) > 0) {
            return $this->listProfile($page, $where);
        }

        $offset = ($page - 1) * 50;

        $query = $this->getQueryBuilder();
        $query->select('d.*')
            ->from('demos', 'd');

        $this->addWhere($query, $where);

        $query->orderBy('d.id', $order)
            ->setMaxResults(50)
            ->setFirstResult($offset);

        $demos = $query->executeQuery()->fetchAllAssociative();

        return $this->formatList($demos);
    }

    /**
     * @param array{
     *     'id': string,
     *     'url': string,
     *     'name': string,
     *     'server': string,
     *     'duration': string,
     *     'nick': string,
     *     'map': string,
     *     'created_at': string,
     *     'red': string,
     *     'blu': string,
     *     'scoreRed': string,
     *     'scoreBlue': string,
     *     'playerCount': string,
     *     'uploader': string,
     *     'hash': string,
     *     'backend': string,
     *     'path': string,
     * }[] $rows
     *
     * @return Demo[]
     */
    protected function formatList(array $rows): array {
        return array_map(function ($row) {
            return Demo::fromRow($row);
        }, $rows);
    }
}
