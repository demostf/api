<?php

declare(strict_types=1);

namespace Demostf\API\Providers;

use PDO;

class InfoProvider extends BaseProvider {
    public function listMaps() {
        $sql = 'SELECT map, count FROM map_list';
        $result = $this->query($sql);

        return $result->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getStats() {
        $demoCount = $this->db->demo()->count();
        $playerCount = $this->db->user()->count();

        return [
            'demos' => $demoCount,
            'players' => $playerCount,
        ];
    }
}
