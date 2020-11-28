<?php

declare(strict_types=1);

namespace Demostf\API\Demo;

use Demostf\API\Data\ParsedDemo;
use Demostf\API\Data\ParsedKill;
use Demostf\API\Data\ParsedPlayer;
use Exception;
use InvalidArgumentException;

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
        if (\is_array($data) && isset($data['intervalPerTick'])) {
            return $this->handleData($data);
        } else {
            throw new InvalidArgumentException('Error parsing demo');
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

        if (!isset($data['rounds'])) {
            throw new Exception("Error while parsing demo, no rounds field found\n" . json_encode($data));
        }
        foreach ($data['rounds'] as $round) {
            if ('red' === $round['winner']) {
                ++$red;
            } else {
                ++$blue;
            }
        }

        if (!isset($data['chat'])) {
            throw new Exception('Error while parsing demo, no chat field found');
        }
        foreach ($data['chat'] as $message) {
            if (isset($message['from'])) {
                $chat[] = new ChatMessage($message['from'],
                    (int) floor(($message['tick'] - $data['startTick']) * $intervalPerTick), $message['text']);
            }
        }

        if (!isset($data['users'])) {
            throw new Exception('Error while parsing demo, no users field found');
        }

        $deaths = array_filter($data['deaths'], function ($death) {
            return $death['killer'] !== $death['victim'];
        });
        /** @var ParsedKill[] $kills */
        $kills = array_map(function (array $death) {
            return new ParsedKill($death['killer'] ?? 0, $death['assister'] ?? 0, $death['victim'] ?? 0,
                $death['weapon']);
        }, $deaths);
        $activityMap = [];
        foreach ($kills as $kill) {
            if (!isset($activityMap[$kill->getAttackerDemoId()])) {
                $activityMap[$kill->getAttackerDemoId()] = 0;
            }
            ++$activityMap[$kill->getAttackerDemoId()];
            if (!isset($activityMap[$kill->getAssisterDemoId()])) {
                $activityMap[$kill->getAssisterDemoId()] = 0;
            }
            ++$activityMap[$kill->getAssisterDemoId()];
            if (!isset($activityMap[$kill->getVictimDemoId()])) {
                $activityMap[$kill->getVictimDemoId()] = 0;
            }
            ++$activityMap[$kill->getVictimDemoId()];
        }

        foreach ($data['users'] as $player) {
            $class = 0;
            $classSpawns = 0;
            if (!isset($activityMap[$player['userId']])) {
                // skip players with no kills, assists or deaths
                continue;
            }
            foreach ($player['classes'] as $classId => $spawns) {
                if ($spawns > $classSpawns) {
                    $classSpawns = $spawns;
                    $class = $classId;
                }
            }
            if ($player['steamId'] && 'BOT' !== $player['steamId']) {//skip spectators
                $players[] = new ParsedPlayer(
                    $player['name'],
                    $player['userId'],
                    self::convertSteamIdToCommunityId($player['steamId']),
                    $player['team'] ?? '',
                    $this->getClassName((int) $class)
                );
            }
        }

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
     * @throws InvalidArgumentException if the SteamID doesn't have the correct
     *                                  format
     *
     * @return string The converted 64bit numeric SteamID
     */
    public static function convertSteamIdToCommunityId(string $steamId): string {
        if ('STEAM_ID_LAN' === $steamId || 'BOT' === $steamId) {
            throw new InvalidArgumentException("Cannot convert SteamID \"$steamId\" to a community ID.");
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
            throw new InvalidArgumentException("SteamID \"$steamId\" doesn't have the correct format.");
        }
    }
}
