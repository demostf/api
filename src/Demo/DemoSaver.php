<?php

declare(strict_types=1);

namespace Demostf\API\Demo;

use DateTime;
use Demostf\API\Data\ParsedDemo;
use Demostf\API\Data\Player;
use Demostf\API\Data\StoredDemo;
use Demostf\API\Data\Upload;
use Demostf\API\Providers\ChatProvider;
use Demostf\API\Providers\DemoProvider;
use Demostf\API\Providers\PlayerProvider;
use Demostf\API\Providers\UserProvider;
use Doctrine\DBAL\Connection;

class DemoSaver {
    private PlayerProvider $playerProvider;
    private ChatProvider $chatProvider;
    private UserProvider $userProvider;
    private DemoProvider $demoProvider;
    private Connection $connection;

    public function __construct(
        PlayerProvider $playerProvider,
        ChatProvider $chatProvider,
        UserProvider $userProvider,
        DemoProvider $demoProvider,
        Connection $connection
    ) {
        $this->playerProvider = $playerProvider;
        $this->chatProvider = $chatProvider;
        $this->userProvider = $userProvider;
        $this->demoProvider = $demoProvider;
        $this->connection = $connection;
    }

    public function saveDemo(ParsedDemo $demo, Header $header, StoredDemo $storedDemo, Upload $upload): int {
        $this->connection->beginTransaction();
        $now = new \DateTimeImmutable();
        $week = \DateInterval::createFromDateString('7 days');
        $privateUntil = $upload->isPrivate() ? $now->add($week) : null;

        $demoId = $this->demoProvider->storeDemo(new Demo(
            0,
            $storedDemo->getUrl(),
            $upload->getName(),
            $header->getServer(),
            $header->getDuration(),
            $header->getNick(),
            $header->getMap(),
            new DateTime(),
            $upload->getRed(),
            $upload->getBlue(),
            $demo->getRedScore(),
            $demo->getBlueScore(),
            \count($demo->getPlayers()),
            $upload->getUploaderId(),
            $upload->getHash(),
            $storedDemo->getBackend(),
            $storedDemo->getPath(),
            $privateUntil,
        ), $storedDemo->getBackend(), $storedDemo->getPath());

        $kills = [];
        $assists = [];
        $deaths = [];

        foreach ($demo->getPlayers() as $player) {
            foreach ($player->getDemoUserIds() as $demoUserId) {
                $kills[$demoUserId] = 0;
                $assists[$demoUserId] = 0;
                $deaths[$demoUserId] = 0;
            }
        }

        foreach ($demo->getKills() as $kill) {
            if ($kill->getAttackerDemoId() && isset($kills[$kill->getAttackerDemoId()])) {
                ++$kills[$kill->getAttackerDemoId()];
            }
            if ($kill->getAssisterDemoId() && isset($kills[$kill->getAssisterDemoId()])) {
                ++$assists[$kill->getAssisterDemoId()];
            }
            if ($kill->getVictimDemoId() && isset($kills[$kill->getVictimDemoId()])) {
                ++$deaths[$kill->getVictimDemoId()];
            }
        }

        foreach ($demo->getPlayers() as $player) {
            $userId = $this->userProvider->getUserId($player->getSteamId(), $player->getName());

            $playerKills = 0;
            $playerAssists = 0;
            $playerDeaths = 0;
            foreach ($player->getDemoUserIds() as $demoUserId) {
                $playerKills += $kills[$demoUserId];
                $playerAssists += $assists[$demoUserId];
                $playerDeaths += $deaths[$demoUserId];
            }

            $this->playerProvider->store(new Player(
                0,
                $demoId,
                $player->getDemoUserIds()[0],
                $userId,
                $player->getName(),
                $player->getTeam(),
                $player->getClass(),
                $playerKills,
                $playerAssists,
                $playerDeaths
            ));
        }

        foreach ($demo->getChat() as $chat) {
            $this->chatProvider->storeChatMessage($demoId, $chat);
        }

        $this->connection->commit();

        return $demoId;
    }
}
