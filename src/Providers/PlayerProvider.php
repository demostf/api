<?php declare(strict_types=1);

namespace Demostf\API\Providers;

use Demostf\API\Data\Player;

class PlayerProvider extends BaseProvider {
    public function store(Player $player): int {
        $query = $this->getQueryBuilder();
        $query->insert('players')
            ->values([
                'demo_id' => $query->createNamedParameter($player->getDemoId()),
                'demo_user_id' => $query->createNamedParameter($player->getDemoUserId()),
                'user_id' => $query->createNamedParameter($player->getUserId()),
                'name' => $query->createNamedParameter($player->getName()),
                'team' => $query->createNamedParameter($player->getTeam()),
                'class' => $query->createNamedParameter($player->getClass()),
                'created_at' => 'now()',
                'updated_at' => 'now()'
            ]);
        $query->execute();

        return (int)$this->connection->lastInsertId();
    }
}
