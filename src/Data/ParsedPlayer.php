<?php

declare(strict_types=1);

namespace Demostf\API\Data;

class ParsedPlayer {
    private string $name;
    private int $demoUserId;
    private string $steamId;
    private string $team;
    private string $class;

    public function __construct(string $name, int $demoUserId, string $steamId, string $team, string $class) {
        $this->name = $name;
        $this->demoUserId = $demoUserId;
        $this->steamId = $steamId;
        $this->team = $team;
        $this->class = $class;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getDemoUserId(): int {
        return $this->demoUserId;
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
