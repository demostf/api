<?php

declare(strict_types=1);

namespace Demostf\API\Providers;

use Demostf\API\Demo\Demo;
use Doctrine\DBAL\Connection;

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
        $in = implode(', ', array_fill(0, count($userIds), '?'));

        $sql = 'SELECT players.demo_id FROM players WHERE players.user_id IN (' . $in . ') 
        GROUP BY players.demo_id HAVING COUNT(user_id) = ? ORDER BY players.demo_id DESC LIMIT 50 OFFSET ' . ((int) $page - 1) * 50;

        $params = $userIds;
        $params[] = count($userIds);

        $result = $this->query($sql, $params);
        $demoIds = $result->fetchAll(\PDO::FETCH_COLUMN);

        $demos = $this->db->demo()->where('id', $demoIds)
            ->where($where)
            ->orderBy('id', 'DESC');

        return $this->formatList($demos->fetchAll());
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
        if (isset($where['map'])) {
            $query->where($query->expr()->orX(
                $query->expr()->eq('clean_map_name(map)', $query->createNamedParameter($where['map'])),
                $query->expr()->eq('map', $query->createNamedParameter($where['map']))
            ));
        }
        if (isset($where['playerCount'])) {
            $query->where($query->expr()->in('"playerCount"',
                $query->createNamedParameter($where['playerCount'], Connection::PARAM_INT_ARRAY)));
        }
        if (isset($where['uploader'])) {
            $query->where($query->expr()->in('uploader',
                $query->createNamedParameter($where['uploader'], \PDO::PARAM_INT)));
        }
        if (isset($where['backend'])) {
            $query->where($query->expr()->eq('backend',
                $query->createNamedParameter($where['backend'])));
        }
        $query->orderBy('d.id', $order)
            ->setMaxResults(50)
            ->setFirstResult($offset);

        $demos = $query->execute()->fetchAll(\PDO::FETCH_ASSOC);

        return $this->formatList($demos);
    }

    protected function formatList(array $rows) {
        return array_map(function ($row) {
            return Demo::fromRow($row);
        }, $rows);
    }
}
