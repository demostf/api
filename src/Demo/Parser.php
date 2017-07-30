<?php

declare(strict_types=1);

namespace Demostf\API\Demo;

use Demostf\API\Data\ParsedDemo;
use Demostf\API\Data\ParsedKill;
use Demostf\API\Data\ParsedPlayer;

/**
 * Higher level parser.
 *
 * Processes the raw demo.js output to something more suitable for our purpose
 */
class Parser {
    const CLASSES = [
        1 => 'scout',
        2 => 'sniper',
        3 => 'soldier',
        4 => 'demoman',
        5 => 'medic',
        6 => 'heavyweapons',
        7 => 'pyro',
        8 => 'spy',
        9 => 'engineer',
    ];

    /** @var RawParser */
    private $rawParser;

    public function __construct(RawParser $rawParser) {
        $this->rawParser = $rawParser;
    }

    public function analyse(string $path): ParsedDemo {
        $data = $this->rawParser->parse($path);
        if (is_array($data)) {
            return $this->handleData($data);
        } else {
            throw new \InvalidArgumentException('Error parsing demo');
        }
    }

    private function handleData(array $data): ParsedDemo {
        $intervalPerTick = $data['intervalPerTick'];
        $red = 0;
        $blue = 0;
        /** @var ChatMessage[] $chat */
        $chat = [];
        /** @var ParsedPlayer[] $players */
        $players = [];
        foreach ($data['rounds'] as $round) {
            if ($round['winner'] === 'red') {
                ++$red;
            } else {
                ++$blue;
            }
        }

        foreach ($data['chat'] as $message) {
            if (isset($message['from'])) {
                $chat[] = new ChatMessage($message['from'],
                    (int)floor(($message['tick'] - $data['startTick']) * $intervalPerTick), $message['text']);
            }
        }

        foreach ($data['users'] as $player) {
            $class = 0;
            $classSpawns = 0;
            foreach ($player['classes'] as $classId => $spawns) {
                if ($spawns > $classSpawns) {
                    $classSpawns = $spawns;
                    $class = $classId;
                }
            }
            if ($class && $player['steamId']) {//skip spectators
                $players[] = new ParsedPlayer(
                    $player['name'],
                    $player['userId'],
                    $this->convertSteamIdToCommunityId($player['steamId']),
                    $player['team'],
                    $this->getClassName((int)$class)
                );
            }
        }

        $kills = array_map(function (array $death) {
            return new ParsedKill($death['killer'] ?? 0, $death['assister'] ?? 0, $death['victim'] ?? 0,
                $death['weapon']);
        }, $data['deaths']);

        return new ParsedDemo(
            $red,
            $blue,
            $chat,
            $players,
            $kills
        );
    }

    private function getClassName(int $classId): string {
        return self::CLASSES[$classId] ?? 'Unknown';
    }

    /**
     * Credit to https://github.com/koraktor/steam-condenser-php.
     *
     * Converts a SteamID as reported by game servers to a 64bit numeric
     * SteamID as used by the Steam Community
     *
     * @param string $steamId The SteamID string as used on servers, like
     *                        <var>STEAM_0:0:12345</var>
     *
     * @throws \InvalidArgumentException if the SteamID doesn't have the correct
     *                                   format
     *
     * @return string The converted 64bit numeric SteamID
     */
    public function convertSteamIdToCommunityId(string $steamId): string {
        if ($steamId === 'STEAM_ID_LAN' || $steamId === 'BOT') {
            throw new \InvalidArgumentException("Cannot convert SteamID \"$steamId\" to a community ID.");
        }
        if (preg_match('/^STEAM_[0-1]:[0-1]:[0-9]+$/', $steamId)) {
            $steamParts = explode(':', substr($steamId, 8));
            $steamId = $steamParts[0] + $steamParts[1] * 2 + 1197960265728;

            return '7656' . $steamId;
        } elseif (preg_match('/^\[U:[0-1]:[0-9]+\]$/', $steamId)) {
            $steamParts = explode(':', substr($steamId, 3, -1));
            $steamId = $steamParts[0] + $steamParts[1] + 1197960265727;

            return '7656' . $steamId;
        } else {
            throw new \InvalidArgumentException("SteamID \"$steamId\" doesn't have the correct format.");
        }
    }
}
