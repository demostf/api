<?php

declare(strict_types=1);

namespace Demostf\API\Demo;

class ChatMessage implements \JsonSerializable {
    /** @var string */
    private $user;

    /** @var int */
    private $time;

    /** @var string */
    private $message;

    /**
     * ChatMessage constructor.
     *
     * @param string $user
     * @param int    $time
     * @param string $message
     */
    public function __construct(string $user, int $time, string $message) {
        $this->user = $user;
        $this->time = $time;
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getUser(): string {
        return $this->user;
    }

    /**
     * @return int
     */
    public function getTime(): int {
        return $this->time;
    }

    /**
     * @return string
     */
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
