<?php

declare(strict_types=1);

namespace Demostf\API\Providers;

use Demostf\API\Data\Kill;

class KillProvider extends BaseProvider {
    public function store(Kill $kill): int {
        $query = $this->getQueryBuilder();
        $query->insert('kills')
            ->values([
                'demo_id' => $query->createNamedParameter($kill->getDemoId()),
                'attacker_id' => $query->createNamedParameter($kill->getAttackerId()),
                'assister_id' => $query->createNamedParameter($kill->getAssisterId()),
                'victim_id' => $query->createNamedParameter($kill->getVictimId()),
                'weapon' => $query->createNamedParameter($kill->getWeapon()),
                'created_at' => 'now()',
                'updated_at' => 'now()',
            ]);
        $query->execute();

        return (int) $this->connection->lastInsertId();
    }
}
