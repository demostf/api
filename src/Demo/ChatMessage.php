<?php

declare(strict_types=1);

namespace Demostf\API\Demo;

use JsonSerializable;

class ChatMessage implements JsonSerializable {
    private string $user;
    private int $time;
    private string $message;

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

    /**
     * @return array{'user': string, 'time': int, 'message': string}
     */
    public function jsonSerialize(): array {
        return [
            'user' => $this->user,
            'time' => $this->time,
            'message' => $this->message,
        ];
    }

    /**
     * @param array{'from': string, 'time': string, 'text': string} $row
     */
    public static function fromRow(array $row): self {
        return new self(
            $row['from'],
            (int) $row['time'],
            $row['text']
        );
    }
}
