<?php

declare(strict_types=1);

namespace Demostf\API\Test\Demo;

use Demostf\API\Data\DemoPlayer;
use Demostf\API\Data\ParsedDemo;
use Demostf\API\Data\ParsedKill;
use Demostf\API\Data\ParsedPlayer;
use Demostf\API\Data\StoredDemo;
use Demostf\API\Data\Upload;
use Demostf\API\Demo\ChatMessage;
use Demostf\API\Demo\DemoSaver;
use Demostf\API\Demo\Header;
use Demostf\API\Providers\ChatProvider;
use Demostf\API\Providers\DemoProvider;
use Demostf\API\Providers\PlayerProvider;
use Demostf\API\Providers\UserProvider;
use Demostf\API\Test\TestCase;
use Doctrine\DBAL\Connection;

class DemoSaverTest extends TestCase {
    public function testSave() {
        $steamId1 = $this->getSteamId('1234567', 'user1');
        $steamId2 = $this->getSteamId('2345678', 'user2');

        $userProvider = new UserProvider($this->getDatabaseConnection(), $this->getRandomGenerator());
        $demoProvider = new DemoProvider($this->getDatabaseConnection(), $userProvider);
        $chatProvider = new ChatProvider($this->getDatabaseConnection());

        $userProvider->store($steamId1, 'user1');
        $userProvider->store($steamId2, 'user2');

        $upload = new Upload(
            'foodemo',
            'DER',
            'ULB',
            $userProvider->getUserId('2345678', 'user2'),
            'securehash',
            false,
        );

        $header = new Header(
            'HL2DEMO',
            12,
            13,
            'My Server',
            'STV',
            'pl_badwater',
            'tf',
            60,
            60 * 60,
            2,
            1
        );

        $parsed = new ParsedDemo(
            2,
            3,
            [
                new ChatMessage('user1', 12, 'msg1'),
                new ChatMessage('user2', 13, 'msg2'),
            ],
            [
                (new ParsedPlayer('user1', '1234567', 'red', 'scout'))->addDemoUserId(1),
                (new ParsedPlayer('user2', '2345678', 'blue', 'soldier'))->addDemoUserId(2),
            ],
            [
                new ParsedKill(1, 0, 2),
                new ParsedKill(1, 2, 2),
                new ParsedKill(2, 0, 1),
            ]
        );

        $saver = new DemoSaver(
            new PlayerProvider($this->getDatabaseConnection()),
            $chatProvider,
            $userProvider,
            $demoProvider,
            $this->createMock(Connection::class)
        );

        $storedDemo = new StoredDemo('https://example.com/foo', 'foo', 'example');

        $demoId = $saver->saveDemo($parsed, $header, $storedDemo, $upload);

        $retrievedDemo = $demoProvider->get($demoId);

        $this->assertEquals(2, $retrievedDemo->getPlayerCount());
        $this->assertEquals(2, $retrievedDemo->getRedScore());
        $this->assertEquals(3, $retrievedDemo->getBlueScore());
        $this->assertEquals('DER', $retrievedDemo->getRed());
        $this->assertEquals('ULB', $retrievedDemo->getBlue());

        $this->assertEquals('user2', $retrievedDemo->getUploaderUser()->getName());

        $players = $retrievedDemo->getPlayers();
        usort($players, function (DemoPlayer $a, DemoPlayer $b) {
            return $a->getName() <=> $b->getName();
        });

        $this->assertEquals('user1', $players[0]->getName());
        $this->assertEquals(2, $players[0]->getKills());
        $this->assertEquals(0, $players[0]->getAssists());
        $this->assertEquals(1, $players[0]->getDeaths());
        $this->assertEquals('red', $players[0]->getTeam());
        $this->assertEquals('scout', $players[0]->getClass());

        $this->assertEquals('user2', $players[1]->getName());
        $this->assertEquals(1, $players[1]->getKills());
        $this->assertEquals(1, $players[1]->getAssists());
        $this->assertEquals(2, $players[1]->getDeaths());
        $this->assertEquals('blue', $players[1]->getTeam());
        $this->assertEquals('soldier', $players[1]->getClass());

        $this->assertEquals([
            new ChatMessage('user1', 12, 'msg1'),
            new ChatMessage('user2', 13, 'msg2'),
        ], $chatProvider->getChat($demoId));
    }
}
