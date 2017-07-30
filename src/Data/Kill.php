<?php

declare(strict_types=1);

namespace Demostf\API\Data;

class Kill {
    private $id;

    private $demoId;

    private $attackerId;

    private $assisterId;

    private $victimId;

    private $weapon;

    public function __construct(int $id, int $demoId, int $attackerId, int $assisterId, int $victimId, string $weapon) {
        $this->id = $id;
        $this->demoId = $demoId;
        $this->attackerId = $attackerId;
        $this->assisterId = $assisterId;
        $this->victimId = $victimId;
        $this->weapon = $weapon;
    }

    public function getId(): int {
        return $this->id;
    }

    public function getDemoId(): int {
        return $this->demoId;
    }

    public function getAttackerId(): int {
        return $this->attackerId;
    }

    public function getAssisterId(): int {
        return $this->assisterId;
    }

    public function getVictimId(): int {
        return $this->victimId;
    }

    public function getWeapon(): string {
        return $this->weapon;
    }
}
