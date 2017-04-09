<?php declare(strict_types=1);

namespace Demostf\API\Test\Providers;

use Demostf\API\Providers\UserProvider;
use Demostf\API\Test\TestCase;

class UserProviderTest extends TestCase {
	/** @var UserProvider */
	private $provider;

	/** @var \SteamId */
	private $steamId;

	public function setUp() {
		parent::setUp();

		$this->steamId = $this->getSteamId('76561198024494988', 'Icewind');
		$this->provider = new UserProvider($this->getDatabaseConnection(), $this->getRandomGenerator());
	}

	public function testGetNonExisting() {
		$this->assertNull($this->provider->get('76561198024494988'));
	}

	public function testStoreRetrieve() {
		$this->provider->store($this->steamId);

		$user = $this->provider->get('76561198024494988');

		$this->assertEquals($this->steamId->getNickname(), $user->getName());
		$this->assertEquals($this->steamId->getSteamId64(), $user->getSteamId());
	}

	public function returnTokenExisting() {
		$token1 = $this->provider->store($this->steamId);
		$token2 = $this->provider->store($this->steamId);

		$this->assertEquals($token1, $token2);
	}

	public function testDoubleInsert() {
		$this->provider->store($this->steamId);
		$this->provider->store($this->steamId);

		$this->assertTrue(true);
	}

	public function testByKey() {
		$token = $this->provider->store($this->steamId);

		$byKey = $this->provider->byKey($token);
		$this->assertEquals('76561198024494988', $byKey->getSteamId());
	}

	public function testSearch() {
		$result = $this->provider->search('__NOT__FOUND__');

		$this->assertCount(0, $result);
	}

	public function testGetIdExisting() {
		$this->provider->store($this->steamId);

		$user = $this->provider->get($this->steamId->getSteamId64());

		$this->assertEquals($user->getId(), $this->provider->getUserId($this->steamId->getSteamId64()));
	}

	public function testGetIdNew() {
		$id = $this->provider->getUserId($this->steamId->getSteamId64());

		$user = $this->provider->get($this->steamId->getSteamId64());


		$this->assertEquals($user->getId(), $id);
	}
}
