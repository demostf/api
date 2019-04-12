<?php

declare(strict_types=1);

namespace Demostf\API\Test\Providers;

use Demostf\API\Data\Player;
use Demostf\API\Demo\Demo;
use Demostf\API\Providers\DemoListProvider;
use Demostf\API\Providers\DemoProvider;
use Demostf\API\Providers\PlayerProvider;
use Demostf\API\Providers\UserProvider;
use Demostf\API\Test\TestCase;

class DemoListProviderTest extends TestCase {
    /** @var DemoListProvider */
    private $demoListProvider;
    /** @var DemoProvider */
    private $demoProvider;
    /** @var PlayerProvider */
    private $playerProvider;
    /** @var UserProvider */
    private $userProvider;

    public function setUp(): void {
        parent::setUp();

        $this->demoListProvider = new DemoListProvider($this->getDatabaseConnection());
        $this->demoProvider = new DemoProvider($this->getDatabaseConnection());
        $this->playerProvider = new PlayerProvider($this->getDatabaseConnection());
        $this->userProvider = new UserProvider($this->getDatabaseConnection(), $this->getRandomGenerator());
    }

    private function getDemo(int $uploaderId, $map = 'map', $playerCount = 18) {
        return new Demo(
            0,
            'http://example.com',
            'name',
            'server',
            12,
            'nick',
            $map,
            new \DateTime(),
            'RED',
            'BLUE',
            1,
            2,
            $playerCount,
            $uploaderId,
            'hash',
            'backend',
            'path'
        );
    }

    public function testListEmpty() {
        $this->assertEquals([], $this->demoListProvider->listDemos(1));
    }

    public function testListSimple() {
        $id1 = $this->demoProvider->storeDemo($this->getDemo(1), 'foo', 'bar');
        $id2 = $this->demoProvider->storeDemo($this->getDemo(1), 'foo', 'bar');
        $id3 = $this->demoProvider->storeDemo($this->getDemo(1), 'foo', 'bar');

        $list = $this->demoListProvider->listDemos(1);
        $this->assertCount(3, $list);

        $this->assertEquals($id3, $list[0]->getId());
        $this->assertEquals($id2, $list[1]->getId());
        $this->assertEquals($id1, $list[2]->getId());
    }

    public function testFilterMap() {
        $id1 = $this->demoProvider->storeDemo($this->getDemo(1, 'map_foo'), 'foo', 'bar');
        $id2 = $this->demoProvider->storeDemo($this->getDemo(1, 'map_bar'), 'foo', 'bar');
        $id3 = $this->demoProvider->storeDemo($this->getDemo(1, 'map_foo'), 'foo', 'bar');

        $list = $this->demoListProvider->listDemos(1, ['map' => 'map_foo']);
        $this->assertCount(2, $list);

        $this->assertEquals($id3, $list[0]->getId());
        $this->assertEquals($id1, $list[1]->getId());
    }

    public function testFilterMapCleaned() {
        $id1 = $this->demoProvider->storeDemo($this->getDemo(1, 'map_foo_b2'), 'foo', 'bar');
        $id2 = $this->demoProvider->storeDemo($this->getDemo(1, 'map_bar'), 'foo', 'bar');
        $id3 = $this->demoProvider->storeDemo($this->getDemo(1, 'map_foo'), 'foo', 'bar');
        $id4 = $this->demoProvider->storeDemo($this->getDemo(1, 'map_foo_final1'), 'foo', 'bar');

        $list = $this->demoListProvider->listDemos(1, ['map' => 'map_foo']);
        $this->assertCount(3, $list);

        $this->assertEquals($id4, $list[0]->getId());
        $this->assertEquals($id3, $list[1]->getId());
        $this->assertEquals($id1, $list[2]->getId());
    }

    public function testFilterPlayerCount() {
        $id1 = $this->demoProvider->storeDemo($this->getDemo(1, 'map1', 17), 'foo', 'bar');
        $id2 = $this->demoProvider->storeDemo($this->getDemo(1, 'map2', 18), 'foo', 'bar');
        $id3 = $this->demoProvider->storeDemo($this->getDemo(1, 'map1', 12), 'foo', 'bar');

        $list = $this->demoListProvider->listDemos(1, ['playerCount' => [17, 18, 19]]);
        $this->assertCount(2, $list);

        $this->assertEquals($id2, $list[0]->getId());
        $this->assertEquals($id1, $list[1]->getId());
    }

    public function testByUploader() {
        $steamId = $this->getSteamId('12345', 'bar');
        $this->userProvider->store($steamId);
        $userId = $this->userProvider->get($steamId->getSteamId64())->getId();
        $id1 = $this->demoProvider->storeDemo($this->getDemo($userId, 'map1', 17), 'foo', 'bar');
        $id2 = $this->demoProvider->storeDemo($this->getDemo($userId, 'map2', 18), 'foo', 'bar');
        $id3 = $this->demoProvider->storeDemo($this->getDemo($userId + 1, 'map1', 12), 'foo', 'bar');

        $list = $this->demoListProvider->listUploads($steamId->getSteamId64(), 1);

        $this->assertEquals($id2, $list[0]->getId());
        $this->assertEquals($id1, $list[1]->getId());
    }

    public function testByUploaderFilter() {
        $steamId = $this->getSteamId('12345', 'bar');
        $this->userProvider->store($steamId);
        $userId = $this->userProvider->get($steamId->getSteamId64())->getId();
        $id1 = $this->demoProvider->storeDemo($this->getDemo($userId, 'map1', 12), 'foo', 'bar');
        $id2 = $this->demoProvider->storeDemo($this->getDemo($userId, 'map2', 18), 'foo', 'bar');
        $id3 = $this->demoProvider->storeDemo($this->getDemo($userId + 1, 'map1', 12), 'foo', 'bar');

        $list = $this->demoListProvider->listUploads($steamId->getSteamId64(), 1, ['playerCount' => [17, 18, 19]]);

        $this->assertEquals($id2, $list[0]->getId());
    }

    private function addPlayer($demoId, $userId) {
        $player = new Player(0, $demoId, 0, $userId, 'foo', 'red', 'scout');
        $this->playerProvider->store($player);
    }

    public function testFilterPlayer() {
        $steamId1 = $this->getSteamId('12345', 'bar1');
        $steamId2 = $this->getSteamId('22345', 'bar2');
        $steamId3 = $this->getSteamId('32345', 'bar3');
        $this->userProvider->store($steamId1);
        $this->userProvider->store($steamId2);
        $this->userProvider->store($steamId3);
        $userId1 = $this->userProvider->get($steamId1->getSteamId64())->getId();
        $userId2 = $this->userProvider->get($steamId2->getSteamId64())->getId();
        $userId3 = $this->userProvider->get($steamId3->getSteamId64())->getId();

        $id1 = $this->demoProvider->storeDemo($this->getDemo(1, 'map1', 17), 'foo', 'bar');
        $id2 = $this->demoProvider->storeDemo($this->getDemo(1, 'map2', 18), 'foo', 'bar');
        $id3 = $this->demoProvider->storeDemo($this->getDemo(1, 'map1', 12), 'foo', 'bar');

        $this->addPlayer($id1, $userId1);
        $this->addPlayer($id1, $userId2);
        $this->addPlayer($id2, $userId1);
        $this->addPlayer($id2, $userId3);
        $this->addPlayer($id3, $userId3);

        $list = $this->demoListProvider->listDemos(1, ['players' => [$steamId1->getSteamId64()]]);

        $this->assertCount(2, $list);
        $this->assertEquals($id2, $list[0]->getId());
        $this->assertEquals($id1, $list[1]->getId());

        $list = $this->demoListProvider->listDemos(1,
            ['players' => [$steamId1->getSteamId64(), $steamId3->getSteamId64()]]);

        $this->assertCount(1, $list);
        $this->assertEquals($id2, $list[0]->getId());

        $list = $this->demoListProvider->listDemos(1,
            ['players' => [$steamId2->getSteamId64(), $steamId3->getSteamId64()]]);

        $this->assertCount(0, $list);
    }

    public function testByUploaderFilterBackend() {
        $steamId = $this->getSteamId('12345', 'bar');
        $this->userProvider->store($steamId);
        $userId = $this->userProvider->get($steamId->getSteamId64())->getId();
        $id1 = $this->demoProvider->storeDemo($this->getDemo($userId, 'map1', 12), 'foo1', 'bar');
        $id2 = $this->demoProvider->storeDemo($this->getDemo($userId, 'map2', 18), 'foo2', 'bar');
        $id3 = $this->demoProvider->storeDemo($this->getDemo($userId + 1, 'map1', 12), 'foo2', 'bar');

        $list = $this->demoListProvider->listUploads($steamId->getSteamId64(), 1, ['backend' => 'foo2']);

        $this->assertCount(1, $list);
        $this->assertEquals($id2, $list[0]->getId());
    }
}
