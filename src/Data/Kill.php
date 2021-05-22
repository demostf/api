<?php

declare(strict_types=1);

namespace Demostf\API\Data;

class Kill {
    private int $id;
    private int $demoId;
    private int $attackerId;
    private int $assisterId;
    private int $victimId;

    public function __construct(int $id, int $demoId, int $attackerId, int $assisterId, int $victimId) {
        $this->id = $id;
        $this->demoId = $demoId;
        $this->attackerId = $attackerId;
        $this->assisterId = $assisterId;
        $this->victimId = $victimId;
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
}
