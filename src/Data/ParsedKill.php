<?php

declare(strict_types=1);

namespace Demostf\API\Data;

class ParsedKill {
    private int $attackerDemoId;
    private int $assisterDemoId;
    private int $victimDemoId;
    private string $weapon;

    public function __construct(int $attackerDemoId, int $assisterDemoId, int $victimDemoId, string $weapon) {
        $this->attackerDemoId = $attackerDemoId;
        $this->assisterDemoId = $assisterDemoId;
        $this->victimDemoId = $victimDemoId;
        $this->weapon = $weapon;
    }

    public function getAttackerDemoId(): int {
        return $this->attackerDemoId;
    }

    public function getAssisterDemoId(): int {
        return $this->assisterDemoId;
    }

    public function getVictimDemoId(): int {
        return $this->victimDemoId;
    }

    public function getWeapon(): string {
        return $this->weapon;
    }
}
