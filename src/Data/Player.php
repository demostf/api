<?php declare(strict_types=1);

namespace Demostf\API\Data;

class Player {
    /** @var int */
    private $id;

    /** @var int */
    private $demoId;

    /** @var int */
    private $demoUserId;

    /** @var int */
    private $userId;

    /** @var string */
    private $name;

    /** @var string */
    private $team;

    /** @var string */
    private $class;

    public function __construct(int $id, int $demoId, int $demoUserId, int $userId, string $name, string $team, string $class) {
        $this->id = $id;
        $this->demoId = $demoId;
        $this->demoUserId = $demoUserId;
        $this->userId = $userId;
        $this->name = $name;
        $this->team = $team;
        $this->class = $class;
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
}
