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
    private ?array $players;
    private string $hash;
    private string $backend;
    private string $path;
    private bool $showPrivateData = false;

    private ?\DateTimeImmutable $privateUntil;

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
        string $path,
        ?\DateTimeImmutable $privateUntil,
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
        $this->players = null;
        $this->uploaderUser = null;
        $this->privateUntil = $privateUntil;
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
     * @param array{
     *     'id': string,
     *     'url': string,
     *     'name': string,
     *     'server': string,
     *     'duration': string,
     *     'nick': string,
     *     'map': string,
     *     'created_at': string,
     *     'red': string,
     *     'blu': string,
     *     'scoreRed': string,
     *     'scoreBlue': string,
     *     'playerCount': string,
     *     'uploader': string,
     *     'hash': string,
     *     'backend': string,
     *     'path': string,
     *     'private_until': ?string,
     * } $row
     *
     * @return Demo
     */
    public static function fromRow(array $row): self {
        $private = $row['private_until'];
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
            $row['path'],
            $private ? new \DateTimeImmutable($private) : null,
        );
    }

    /**
     * @return DemoPlayer[]|null
     */
    public function getPlayers(): ?array {
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

    public function getPrivateUntil(): ?\DateTimeImmutable {
        return $this->privateUntil;
    }

    /**
     * @return array{
     *     'id': int,
     *     'url': string,
     *     'name': string,
     *     'server': string,
     *     'duration': float,
     *     'nick': string,
     *     'map': string,
     *     'time': int,
     *     'red': string,
     *     'blue': string,
     *     'redScore': int,
     *     'blueScore': int,
     *     'playerCount': int,
     *     'uploader': User|int,
     *     'hash': string,
     *     'backend': string,
     *     'path': string,
     *     'players': ?DemoPlayer
     *     'private_until': ?string,
     * }
     */
    public function jsonSerialize(): array {
        $now = new \DateTimeImmutable();
        $isPublic = $this->showPrivateData || ($this->getPrivateUntil() ? $this->getPrivateUntil() <= $now : true);
        $data = [
            'id' => $this->getId(),
            'url' => $isPublic ? $this->getUrl() : '',
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
            'backend' => $isPublic ? $this->getBackend() : '',
            'path' => $isPublic ? $this->getPath() : '',
            'private_until' => $this->getPrivateUntil()?->format(\DateTimeImmutable::ATOM),
        ];
        if (\is_array($this->players)) {
            $data['players'] = $this->getPlayers();
        }

        return $data;
    }

    function showPrivateData(bool $show): void {
        $this->showPrivateData = $show;
    }
}
