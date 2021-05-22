<?php

declare(strict_types=1);

namespace Demostf\API\Test\Providers;

use Demostf\API\Data\DemoPlayer;
use Demostf\API\Data\Kill;
use Demostf\API\Data\Player;
use Demostf\API\Demo\Demo;
use Demostf\API\Providers\DemoProvider;
use Demostf\API\Providers\KillProvider;
use Demostf\API\Providers\PlayerProvider;
use Demostf\API\Providers\UserProvider;
use Demostf\API\Test\TestCase;

class DemoProviderTest extends TestCase {
    /** @var DemoProvider */
    private $provider;

    /** @var UserProvider */
    private $userProvider;

    /** @var PlayerProvider */
    private $playerProvider;

    /** @var KillProvider */
    private $killProvider;

    public function setUp(): void {
        parent::setUp();

        $this->userProvider = new UserProvider($this->getDatabaseConnection(), $this->getRandomGenerator());
        $this->provider = new DemoProvider($this->getDatabaseConnection(), $this->userProvider);
        $this->playerProvider = new PlayerProvider($this->getDatabaseConnection());
        $this->killProvider = new KillProvider($this->getDatabaseConnection());
    }

    public function testGetNonExisting() {
        $this->assertNull($this->provider->get(1));
    }

    public function testStoreRetrieve() {
        $uploaderSteamId = $this->getSteamId('12345', 'test');
        $this->userProvider->store($uploaderSteamId, 'test');

        $uploader = $this->userProvider->get($uploaderSteamId->getSteamId64());

        $demo = new Demo(
            0,
            'http://example.com',
            'name',
            'server',
            12,
            'nick',
            'map',
            new \DateTime(),
            'RED',
            'BLUE',
            1,
            2,
            18,
            $uploader->getId(),
            'hash',
            'dummy',
            'path'
        );
        $demo->setUploaderUser($uploader);

        $id = $this->provider->storeDemo($demo, 'dummy', 'path');

        $retrieved = $this->provider->get($id);
        $this->assertInstanceOf(Demo::class, $retrieved);

        $storedData = $demo->jsonSerialize();
        $storedData['id'] = $id;

        $this->assertEquals($storedData, $retrieved->jsonSerialize());

        $this->assertEquals($id, $this->provider->demoIdByHash('hash'));
    }

    public function testRetrieveWithPlayers() {
        $uploaderSteamId = $this->getSteamId('12345', 'test');
        $this->userProvider->store($uploaderSteamId, 'test');

        $steamId1 = $this->getSteamId('1', 'u1');
        $steamId2 = $this->getSteamId('2', 'u2');
        $steamId3 = $this->getSteamId('3', 'u3');
        $steamId4 = $this->getSteamId('4', 'u4');

        $this->userProvider->store($steamId1, 'u1');
        $this->userProvider->store($steamId2, 'u2');
        $this->userProvider->store($steamId3, 'u3');
        $this->userProvider->store($steamId4, 'u4');

        $user1 = $this->userProvider->get($steamId1->getSteamId64());
        $user2 = $this->userProvider->get($steamId2->getSteamId64());
        $user3 = $this->userProvider->get($steamId3->getSteamId64());
        $user4 = $this->userProvider->get($steamId4->getSteamId64());

        $uploader = $this->userProvider->get($uploaderSteamId->getSteamId64());

        $demo = new Demo(
            0,
            'http://example.com',
            'name',
            'server',
            12,
            'nick',
            'map',
            new \DateTime(),
            'RED',
            'BLUE',
            1,
            2,
            18,
            $uploader->getId(),
            'hash',
            'backend',
            'path',
            'dummy',
            'path'
        );

        $id = $this->provider->storeDemo($demo, 'dummy', 'path');
        $player1 = $this->addPlayer($id, 101, $user1->getId(), 'red', 'scout');
        $player2 = $this->addPlayer($id, 102, $user2->getId(), 'red', 'soldier');
        $player3 = $this->addPlayer($id, 103, $user3->getId(), 'blue', 'engineer');
        $player4 = $this->addPlayer($id, 104, $user4->getId(), 'blue', 'spy');

        $this->addKill($id, $user1->getId(), 0, $user3->getId());
        $this->addKill($id, $user1->getId(), $user2->getId(), $user3->getId());
        $this->addKill($id, $user4->getId(), 0, $user1->getId());

        $retrieved = $this->provider->get($id, true);
        $this->assertInstanceOf(Demo::class, $retrieved);

        $players = $retrieved->getPlayers();
        $this->assertCount(4, $players);
        usort($players, function (DemoPlayer $a, DemoPlayer $b) {
            return $a->getUserId() - $b->getUserId();
        });
        $this->assertEquals([
            new DemoPlayer($player1, $user1->getId(), 'user_' . $user1->getId(), 'red', 'scout', '1', 2, 0, 1),
            new DemoPlayer($player2, $user2->getId(), 'user_' . $user2->getId(), 'red', 'soldier', '2', 0, 1, 0),
            new DemoPlayer($player3, $user3->getId(), 'user_' . $user3->getId(), 'blue', 'engineer', '3', 0, 0, 2),
            new DemoPlayer($player4, $user4->getId(), 'user_' . $user4->getId(), 'blue', 'spy', '4', 1, 0, 0),
        ], $players);
    }

    private function addPlayer(int $demoId, int $demoUserId, int $userId, string $team, string $class): int {
        $player = new Player(0, $demoId, $demoUserId, $userId, 'user_' . $userId, $team, $class);

        return $this->playerProvider->store($player);
    }

    private function addKill(int $demoId, int $attackerId, int $assisterId, int $victimId): int {
        $kill = new Kill(0, $demoId, $attackerId, $assisterId, $victimId);

        return $this->killProvider->store($kill);
    }

    public function testSetDemoUrl() {
        $uploaderSteamId = $this->getSteamId('12345', 'test');
        $this->userProvider->store($uploaderSteamId, 'test');

        $uploader = $this->userProvider->get($uploaderSteamId->getSteamId64());

        $demo = new Demo(
            0,
            'http://example.com',
            'name',
            'server',
            12,
            'nick',
            'map',
            new \DateTime(),
            'RED',
            'BLUE',
            1,
            2,
            18,
            $uploader->getId(),
            'hash',
            'dummy',
            'path'
        );

        $id = $this->provider->storeDemo($demo, 'dummy', 'path');
        $id2 = $this->provider->storeDemo($demo, 'dummy', 'path');

        $this->provider->setDemoUrl($id, 'foobackend', 'http://foo.example.com', 'bar');

        $storedDemo = $this->provider->get($id);
        $this->assertEquals('http://foo.example.com', $storedDemo->getUrl());
        $this->assertEquals('foobackend', $storedDemo->getBackend());
        $this->assertEquals('bar', $storedDemo->getPath());

        $storedDemo2 = $this->provider->get($id2);
        $this->assertEquals('http://example.com', $storedDemo2->getUrl());
    }
}
