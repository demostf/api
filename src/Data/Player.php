<?php

declare(strict_types=1);

namespace Demostf\API\Data;

class Player {
    private int $id;
    private int $demoId;
    private int $demoUserId;
    private int $userId;
    private string $name;
    private string $team;
    private string $class;
    private int $kills;
    private int $assists;
    private int $deaths;

    public function __construct(
        int $id,
        int $demoId,
        int $demoUserId,
        int $userId,
        string $name,
        string $team,
        string $class,
        int $kills,
        int $assists,
        int $deaths
    ) {
        $this->id = $id;
        $this->demoId = $demoId;
        $this->demoUserId = $demoUserId;
        $this->userId = $userId;
        $this->name = $name;
        $this->team = $team;
        $this->class = $class;
        $this->kills = $kills;
        $this->assists = $assists;
        $this->deaths = $deaths;
    }

    public function getId(): int {
        return $this->id;
    }

    public function getDemoId(): int {
        return $this->demoId;
    }

    public function getDemoUserId(): int {
        return $this->demoUserId;
    }

    public function getUserId(): int {
        return $this->userId;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getTeam(): string {
        return $this->team;
    }

    public function getClass(): string {
        return $this->class;
    }

    public function getKills(): int {
        return $this->kills;
    }

    public function getAssists(): int {
        return $this->assists;
    }

    public function getDeaths(): int {
        return $this->deaths;
    }
}
