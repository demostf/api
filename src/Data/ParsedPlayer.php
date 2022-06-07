<?php

declare(strict_types=1);

namespace Demostf\API\Data;

class ParsedPlayer {
    private string $name;
    /** @var int[] */
    private array $demoUserIds;
    private string $steamId;
    private string $team;
    private string $class;


    public function __construct(string $name, string $steamId, string $team, string $class) {
        $this->name = $name;
        $this->demoUserIds = [];
        $this->steamId = $steamId;
        $this->team = $team;
        $this->class = $class;
    }

    public function getName(): string {
        return $this->name;
    }

    public function addDemoUserId(int $userId): ParsedPlayer {
        $this->demoUserIds[] = $userId;
        return $this;
    }

    /**
     * @return int[]
     */
    public function getDemoUserIds(): array {
        return $this->demoUserIds;
    }

    public function getSteamId(): string {
        return $this->steamId;
    }

    public function getTeam(): string {
        return $this->team;
    }

    public function getClass(): string {
        return $this->class;
    }
}
