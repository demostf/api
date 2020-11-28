<?php

declare(strict_types=1);

namespace Demostf\API\Data;

use Demostf\API\Demo\ChatMessage;

class ParsedDemo {
    private int $redScore;
    private int $blueScore;
    /** @var ChatMessage[] */
    private array $chat;
    /** @var ParsedPlayer[] */
    private array $players;
    /** @var ParsedKill[] */
    private array $kills;

    /**
     * ParsedDemo constructor.
     *
     * @param ChatMessage[]  $chat
     * @param ParsedPlayer[] $players
     * @param ParsedKill[]   $kills
     */
    public function __construct(int $redScore, int $blueScore, array $chat, array $players, array $kills) {
        $this->redScore = $redScore;
        $this->blueScore = $blueScore;
        $this->chat = $chat;
        $this->players = $players;
        $this->kills = $kills;
    }

    public function getRedScore(): int {
        return $this->redScore;
    }

    public function getBlueScore(): int {
        return $this->blueScore;
    }

    /**
     * @return ChatMessage[]
     */
    public function getChat(): array {
        return $this->chat;
    }

    /**
     * @return ParsedPlayer[]
     */
    public function getPlayers(): array {
        return $this->players;
    }

    /**
     * @return ParsedKill[]
     */
    public function getKills(): array {
        return $this->kills;
    }
}
