<?php

declare(strict_types=1);

namespace Demostf\API\Providers;

class InfoProvider extends BaseProvider {
    /**
     * @return string[]
     */
    public function listMaps(): array {
        $query = $this->getQueryBuilder();
        $query->select('map')
            ->from('map_list')
            ->orderBy('count', 'DESC');
        $result = $query->executeQuery();

        return $result->fetchFirstColumn();
    }

    private function count(string $table): int {
        $query = $this->getQueryBuilder();
        $query->select('count(*)')
            ->from($table);

        return $query->executeQuery()->fetchOne();
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
