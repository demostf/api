<?php

declare(strict_types=1);

namespace Demostf\API\Demo;

/**
 * HL2 demo metadata.
 */
class Header {
    protected string $type;
    protected int $version;
    protected int $protocol;
    protected string $server;
    protected string $nick;
    protected string $map;
    protected string $game;
    protected float $duration;
    protected int $ticks;
    protected int $frames;
    protected int $singOn;

    public function __construct(
        string $type,
        int $version,
        int $protocol,
        string $server,
        string $nick,
        string $map,
        string $game,
        float $duration,
        int $ticks,
        int $frames,
        int $sigon
    ) {
        $this->type = $type;
        $this->version = $version;
        $this->protocol = $protocol;
        $this->server = $server;
        $this->nick = $nick;
        $this->map = strtolower($map);
        $this->game = $game;
        $this->duration = $duration;
        $this->ticks = $ticks;
        $this->frames = $frames;
        $this->singOn = $sigon;
    }

    public function getDuration(): float {
        return $this->duration;
    }

    public function getFrames(): int {
        return $this->frames;
    }

    public function getGame(): string {
        return $this->game;
    }

    public function getMap(): string {
        return $this->map;
    }

    public function getNick(): string {
        return $this->nick;
    }

    public function getProtocol(): int {
        return $this->protocol;
    }

    public function getServer(): string {
        return $this->server;
    }

    public function getSingOn(): int {
        return $this->singOn;
    }

    public function getTicks(): int {
        return $this->ticks;
    }

    public function getType(): string {
        return $this->type;
    }

    public function getVersion(): int {
        return $this->version;
    }

    /**
     * @param array{
     *     'type': string,
     *     'version': int,
     *     'protocol': int,
     *     'server': string,
     *     'nick': string,
     *     'map': string,
     *     'game': string,
     *     'duration': float,
     *     'ticks': int,
     *     'frames': int,
     *     'singon': int,
     * } $info
     *
     * @return Header
     */
    public static function fromArray(array $info): self {
        return new self(
            $info['type'],
            $info['version'],
            $info['protocol'],
            $info['server'],
            $info['nick'],
            $info['map'],
            $info['game'],
            $info['duration'],
            $info['ticks'],
            $info['frames'],
            $info['singon']
        );
    }
}
