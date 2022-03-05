<?php

declare(strict_types=1);

namespace Demostf\API\Providers;

use PDO;

class InfoProvider extends BaseProvider {
    /**
     * @return string[]
     */
    public function listMaps(): array {
        $query = $this->getQueryBuilder();
        $query->select('map')
            ->from('map_list')
            ->orderBy('count', 'DESC');
        $result = $query->execute();

        return $result->fetchAll(PDO::FETCH_COLUMN);
    }

    private function count(string $table): int {
        $query = $this->getQueryBuilder();
        $query->select('count(*)')
            ->from($table);

        return $query->execute()->fetch(PDO::FETCH_COLUMN);
    }

    /**
     * @return int[]
     */
    public function getStats(): array {
        $demoCount = $this->count('demos');
        $playerCount = $this->count('users');

        return [
            'demos' => $demoCount,
            'players' => $playerCount,
        ];
    }
}
