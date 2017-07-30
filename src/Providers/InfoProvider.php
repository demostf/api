<?php namespace Demostf\API\Providers;

class InfoProvider extends BaseProvider {
    public function listMaps() {
        $sql = 'SELECT DISTINCT(map), COUNT(map) AS count from demos GROUP BY map ORDER BY count DESC';
        $result = $this->query($sql);
        return $result->fetchAll(\PDO::FETCH_COLUMN);
    }

    public function getStats() {
        $demoCount = $this->db->demo()->count();
        $playerCount = $this->db->user()->count();

        return [
            'demos' => $demoCount,
            'players' => $playerCount
        ];
    }
}
