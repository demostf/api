<?php

declare(strict_types=1);

namespace Demostf\API\Data;

use JsonSerializable;

class User implements JsonSerializable {
    private int $id;
    private string $steamId;
    private string $name;
    private string $avatar;
    private string $token;

    public function __construct(int $id, string $steamId, string $name, string $avatar, string $token) {
        $this->id = $id;
        $this->steamId = $steamId;
        $this->name = $name;
        $this->avatar = $avatar;
        $this->token = $token;
    }

    public function getId(): int {
        return $this->id;
    }

    public function getSteamId(): string {
        return $this->steamId;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getAvatar(): string {
        return $this->avatar;
    }

    public function getToken(): string {
        return $this->token;
    }

    public function jsonSerialize() {
        return [
            'id' => $this->getId(),
            'steamid' => $this->getSteamId(),
            'name' => $this->getName(),
            'avatar' => $this->getAvatar(),
        ];
    }

    /**
     * @param mixed[] $row
     *
     * @return User
     */
    public static function fromRow(array $row): self {
        return new self(
            (int) $row['id'],
            $row['steamid'],
            $row['name'],
            $row['avatar'],
            $row['token'] ?? ''
        );
    }
}
