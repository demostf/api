<?php

declare(strict_types=1);

namespace Demostf\API\Data;

use Demostf\API\Demo\ChatMessage;

class ParsedDemo {
    /** @var int */
    private $redScore;
    /** @var int */
    private $blueScore;
    /** @var ChatMessage[] */
    private $chat;
    /** @var ParsedPlayer[] */
    private $players;
    /** @var ParsedKill[] */
    private $kills;

    /**
     * ParsedDemo constructor.
     *
     * @param int            $redScore
     * @param int            $blueScore
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
