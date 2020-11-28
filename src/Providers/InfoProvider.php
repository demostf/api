<?php

declare(strict_types=1);

namespace Demostf\API\Providers;

use PDO;

class InfoProvider extends BaseProvider {
    public function listMaps() {
        $query = $this->getQueryBuilder();
        $query->select('map', 'count')
            ->from('map_list');
        $result = $query->execute();

        return $result->fetchAll(PDO::FETCH_COLUMN);
    }

    private function count(string $table): int {
        $query = $this->getQueryBuilder();
        $query->select('count(*)')
            ->from($table);

        return $query->execute()->fetch(PDO::FETCH_COLUMN);
    }

    public function getStats() {
        $demoCount = $this->count('demos');
        $playerCount = $this->count('users');

        return [
            'demos' => $demoCount,
            'players' => $playerCount,
        ];
    }
}
