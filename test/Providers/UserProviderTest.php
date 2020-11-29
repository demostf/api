<?php

declare(strict_types=1);

namespace Demostf\API\Test\Providers;

use Demostf\API\Data\Player;
use Demostf\API\Providers\PlayerProvider;
use Demostf\API\Providers\UserProvider;
use Demostf\API\Test\TestCase;

class UserProviderTest extends TestCase {
    /** @var UserProvider */
    private $provider;
    /** @var PlayerProvider */
    private $playerProvider;

    /** @var \SteamId */
    private $steamId;

    public function setUp(): void {
        parent::setUp();

        $this->steamId = $this->getSteamId('76561198024494988', 'Icewind');
        $this->provider = new UserProvider($this->getDatabaseConnection(), $this->getRandomGenerator());
        $this->playerProvider = new PlayerProvider($this->getDatabaseConnection());
    }

    public function testGetNonExisting() {
        $this->assertNull($this->provider->get('76561198024494988'));
    }

    public function testStoreRetrieve() {
        $this->provider->store($this->steamId, 'Icewind');

        $user = $this->provider->get('76561198024494988');

        $this->assertEquals('Icewind', $user->getName());
        $this->assertEquals($this->steamId->getSteamId64(), $user->getSteamId());
    }

    public function returnTokenExisting() {
        $token1 = $this->provider->store($this->steamId, 'Icewind');
        $token2 = $this->provider->store($this->steamId, 'Icewind');

        $this->assertEquals($token1, $token2);
    }

    public function testDoubleInsert() {
        $this->provider->store($this->steamId, 'Icewind');
        $this->provider->store($this->steamId, 'Icewind');

        $this->assertTrue(true);
    }

    public function testByKey() {
        $token = $this->provider->store($this->steamId, 'Icewind');

        $byKey = $this->provider->byKey($token);
        $this->assertEquals('76561198024494988', $byKey->getSteamId());
    }

    public function testSearch() {
        $result = $this->provider->search('__NOT__FOUND__');

        $this->assertCount(0, $result);

        $this->provider->store($this->steamId, 'Icewind');
        $user = $this->provider->get($this->steamId->getSteamId64());
        $this->playerProvider->store(new Player(
            0,
            1,
            2,
            $user->getId(),
            $user->getName(),
            'red',
            'scout'
        ));

        $this->getDatabaseConnection()->query('REFRESH MATERIALIZED VIEW name_list');

        $result = $this->provider->search('Icewind');
        $this->assertCount(1, $result);
        $this->assertEquals($this->steamId->getSteamId64(), $result[0]->getSteamId());
    }

    public function testGetIdExisting() {
        $this->provider->store($this->steamId, 'Icewind');

        $user = $this->provider->get($this->steamId->getSteamId64());

        $this->assertEquals($user->getId(), $this->provider->getUserId($this->steamId->getSteamId64(), 'Icewind'));
    }

    public function testGetIdNew() {
        $id = $this->provider->getUserId($this->steamId->getSteamId64(), 'Icewind');

        $user = $this->provider->get($this->steamId->getSteamId64());

        $this->assertEquals($user->getId(), $id);
    }
}
