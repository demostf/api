<?php

declare(strict_types=1);

namespace Demostf\API\Data;

use JsonSerializable;

class SteamUser implements JsonSerializable {
    private int $id;
    private string $steamId;
    private string $name;

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

    /**
     * @return array{'id': int, 'name': string, 'steamid': string}
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize() {
        return [
            'id' => $this->getId(),
            'steamid' => $this->getSteamId(),
            'name' => $this->getName(),
        ];
    }

    /**
     * @param mixed[] $row
     *
     * @return SteamUser
     */
    public static function fromRow(array $row): self {
        return new self(
            (int) $row['id'],
            $row['steamid'],
            $row['name']
        );
    }
}
