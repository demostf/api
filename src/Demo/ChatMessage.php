<?php

declare(strict_types=1);

namespace Demostf\API\Demo;

use JsonSerializable;

class ChatMessage implements JsonSerializable {
    /** @var string */
    private $user;

    /** @var int */
    private $time;

    /** @var string */
    private $message;

    /**
     * ChatMessage constructor.
     */
    public function __construct(string $user, int $time, string $message) {
        $this->user = $user;
        $this->time = $time;
        $this->message = $message;
    }

    public function getUser(): string {
        return $this->user;
    }

    public function getTime(): int {
        return $this->time;
    }

    public function getMessage(): string {
        return $this->message;
    }

    public function jsonSerialize() {
        return [
            'user' => $this->user,
            'time' => $this->time,
            'message' => $this->message,
        ];
    }
}
