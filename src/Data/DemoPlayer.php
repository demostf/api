<?php declare(strict_types=1);

namespace Demostf\API\Data;

class DemoPlayer implements \JsonSerializable {
    /** @var int */
    private $id;
    /** @var int */
    private $userId;
    /** @var string */
    private $name;
    /** @var string */
    private $team;
    /** @var string */
    private $class;
    /** @var string */
    private $steamId;
    /** @var string */
    private $avatar;
    /** @var int */
    private $kills;
    /** @var int */
    private $assists;
    /** @var int */
    private $deaths;

    public function __construct(int $id, int $userId, string $name, string $team, string $class, string $steamId, string $avatar, int $kills, int $assists, int $deaths) {
        $this->id = $id;
        $this->userId = $userId;
        $this->name = $name;
        $this->team = $team;
        $this->class = $class;
        $this->steamId = $steamId;
        $this->avatar = $avatar;
        $this->kills = $kills;
        $this->assists = $assists;
        $this->deaths = $deaths;
    }

    public function getId(): int {
        return $this->id;
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

    public function getSteamId(): string {
        return $this->steamId;
    }

    public function getAvatar(): string {
        return $this->avatar;
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

    public static function fromRow($row): DemoPlayer {
        return new DemoPlayer(
            $row['id'],
            $row['user_id'],
            $row['name'],
            $row['team'],
            $row['class'],
            $row['steamid'],
            $row['avatar'],
            $row['kills'],
            $row['assists'],
            $row['deaths']
        );
    }

    public function jsonSerialize() {
        return [
            'id' => $this->getId(),
            'user_id' => $this->getUserId(),
            'name' => $this->getName(),
            'team' => $this->getTeam(),
            'class' => $this->getClass(),
            'steamid' => $this->getSteamId(),
            'avatar' => $this->getAvatar(),
            'kills' => $this->getKills(),
            'assists' => $this->getAssists(),
            'deaths' => $this->getDeaths()
        ];
    }
}
