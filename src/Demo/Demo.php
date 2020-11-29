<?php

declare(strict_types=1);

namespace Demostf\API\Demo;

use DateTime;
use Demostf\API\Data\DemoPlayer;
use Demostf\API\Data\User;
use JsonSerializable;

class Demo implements JsonSerializable {
    private int $id;
    private string $url;
    private string $name;
    private string $server;
    private float $duration;
    private string $nick;
    private string $map;
    private DateTime $time;
    private string $red;
    private string $blue;
    private int $redScore;
    private int $blueScore;
    private int $playerCount;
    private int $uploader;
    private ?User $uploaderUser;
    /** @var DemoPlayer[] */
    private array $players;
    private string $hash;
    private string $backend;
    private string $path;

    public function __construct(
        int $id,
        string $url,
        string $name,
        string $server,
        float $duration,
        string $nick,
        string $map,
        DateTime $time,
        string $red,
        string $blue,
        int $redScore,
        int $blueScore,
        int $playerCount,
        int $uploader,
        string $hash,
        string $backend,
        string $path
    ) {
        $this->id = $id;
        $this->url = $url;
        $this->name = $name;
        $this->server = $server;
        $this->duration = $duration;
        $this->nick = $nick;
        $this->map = $map;
        $this->time = $time;
        $this->red = $red;
        $this->blue = $blue;
        $this->redScore = $redScore;
        $this->blueScore = $blueScore;
        $this->playerCount = $playerCount;
        $this->uploader = $uploader;
        $this->hash = $hash;
        $this->backend = $backend;
        $this->path = $path;
        $this->players = [];
        $this->uploaderUser = null;
    }

    public function getId(): int {
        return $this->id;
    }

    public function getUrl(): string {
        return $this->url;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getServer(): string {
        return $this->server;
    }

    public function getDuration(): float {
        return $this->duration;
    }

    public function getNick(): string {
        return $this->nick;
    }

    public function getMap(): string {
        return $this->map;
    }

    public function getTime(): DateTime {
        return $this->time;
    }

    public function getRed(): string {
        return $this->red;
    }

    public function getBlue(): string {
        return $this->blue;
    }

    public function getRedScore(): int {
        return $this->redScore;
    }

    public function getBlueScore(): int {
        return $this->blueScore;
    }

    public function getPlayerCount(): int {
        return $this->playerCount;
    }

    public function getUploader(): int {
        return $this->uploader;
    }

    public function getUploaderUser(): ?User {
        return $this->uploaderUser;
    }

    public function setUploaderUser(User $uploaderUser): void {
        $this->uploaderUser = $uploaderUser;
    }

    /**
     * @param mixed[] $row
     *
     * @return Demo
     */
    public static function fromRow(array $row): self {
        return new self(
            (int) $row['id'],
            $row['url'],
            $row['name'],
            $row['server'],
            (int) $row['duration'],
            $row['nick'],
            $row['map'],
            DateTime::createFromFormat('U', '' . strtotime($row['created_at'])),
            $row['red'],
            $row['blu'],
            (int) $row['scoreRed'],
            (int) $row['scoreBlue'],
            (int) $row['playerCount'],
            (int) $row['uploader'],
            $row['hash'],
            $row['backend'],
            $row['path']
        );
    }

    /**
     * @return DemoPlayer[]
     */
    public function getPlayers(): array {
        return $this->players;
    }

    /**
     * @param DemoPlayer[] $players
     */
    public function setPlayers(array $players): void {
        $this->players = $players;
    }

    public function getHash(): string {
        return $this->hash;
    }

    public function getBackend(): string {
        return $this->backend;
    }

    public function getPath(): string {
        return $this->path;
    }

    public function jsonSerialize() {
        $data = [
            'id' => $this->getId(),
            'url' => $this->getUrl(),
            'name' => $this->getName(),
            'server' => $this->getServer(),
            'duration' => $this->getDuration(),
            'nick' => $this->getNick(),
            'map' => $this->getMap(),
            'time' => $this->getTime()->getTimestamp(),
            'red' => $this->getRed(),
            'blue' => $this->getBlue(),
            'redScore' => $this->getRedScore(),
            'blueScore' => $this->getBlueScore(),
            'playerCount' => $this->getPlayerCount(),
            'uploader' => $this->uploaderUser ? $this->getUploaderUser()->jsonSerialize() : $this->getUploader(),
            'hash' => $this->getHash(),
            'backend' => $this->getBackend(),
            'path' => $this->getPath(),
        ];
        if ($this->players) {
            $data['players'] = $this->getPlayers();
        }

        return $data;
    }
}
