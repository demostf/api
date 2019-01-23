<?php

declare(strict_types=1);

namespace Demostf\API\Data;

class SteamUser implements \JsonSerializable {
    /** @var int */
    private $id;
    /** @var string */
    private $steamId;
    /** @var string */
    private $name;

    public function __construct(int $id, string $steamId, string $name) {
        $this->id = $id;
        $this->steamId = $steamId;
        $this->name = $name;
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

    public function jsonSerialize() {
        return [
            'id' => $this->getId(),
            'steamid' => $this->getSteamId(),
            'name' => $this->getName(),
        ];
    }

    public static function fromRow(array $row): self {
        return new self(
            (int) $row['id'],
            $row['steamid'],
            $row['name']
        );
    }
}
