<?php

declare(strict_types=1);

namespace Demostf\API\Providers;

use Doctrine\DBAL\Query\QueryBuilder;
use function count;
use Demostf\API\Demo\Demo;
use Doctrine\DBAL\Connection;
use function is_array;
use PDO;

class DemoListProvider extends BaseProvider {
    public function listUploads(string $steamId, int $page, array $where = []) {
        $user = $this->db->user()->where('steamid', $steamId);
        $where['uploader'] = $user->fetch()->id;

        return $this->listDemos($page, $where);
    }

    public function listProfile(int $page, array $where = []) {
        $users = $this->db->user()->where('steamid', $where['players']);
        unset($where['players']);
        $userIds = [];
        foreach ($users as $user) {
            $userIds[] = $user['id'];
        }

        $query = $this->getQueryBuilder();
        $query->select('p.demo_id')
            ->from('players', 'p');

        if (count($userIds) > 1) {
            $query->where($query->expr()->in('user_id',
                $query->createNamedParameter($userIds, Connection::PARAM_INT_ARRAY)))
                ->groupBy('demo_id')
                ->having($query->expr()->eq(
                    'COUNT(user_id)',
                    $query->createNamedParameter(count($userIds), PDO::PARAM_INT)
                ));
        } else {
            $query->where($query->expr()->eq('user_id',
                $query->createNamedParameter($userIds[0], PDO::PARAM_INT)));
        }
        $query->orderBy('demo_id', 'desc')
            ->setMaxResults(50)
            ->setFirstResult(((int) $page - 1) * 50);

        $this->addWhere($query, $where);

        $result = $query->execute();
        $demoIds = $result->fetchAll(PDO::FETCH_COLUMN);

        $demos = $this->db->demo()->where('id', $demoIds)
            ->orderBy('id', 'DESC');

        return $this->formatList($demos->fetchAll());
    }

    private function addWhere(QueryBuilder $query, array $where = []) {
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
                $query->createNamedParameter($where['before']->format(DATE_ATOM))));
        }
        if (isset($where['after'])) {
            $query->andWhere($query->expr()->gt('created_at',
                $query->createNamedParameter($where['after']->format(DATE_ATOM))));
        }
    }

    /**
     * @param int    $page
     * @param array  $where
     * @param string $order
     *
     * @return Demo[]
     */
    public function listDemos(int $page, array $where = [], string $order = 'DESC') {
        if (isset($where['players']) and is_array($where['players']) and count($where['players']) > 0) {
            return $this->listProfile($page, $where);
        }

        $offset = ($page - 1) * 50;

        $query = $this->getQueryBuilder();
        $query->select('d.*')
            ->from('demos', 'd')
            ->leftJoin('d', 'upload_blacklist', 'b', $query->expr()->eq('uploader_id', 'uploader'))
            ->where($query->expr()->isNull('b.id'));

        $this->addWhere($query, $where);

        $query->orderBy('d.id', $order)
            ->setMaxResults(50)
            ->setFirstResult($offset);

        $demos = $query->execute()->fetchAll(PDO::FETCH_ASSOC);

        return $this->formatList($demos);
    }

    protected function formatList(array $rows) {
        return array_map(function ($row) {
            return Demo::fromRow($row);
        }, $rows);
    }
}
