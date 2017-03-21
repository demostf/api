<?php declare(strict_types = 1);
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

		$this->steamId = new \SteamId('76561198024494988', false);
		$closure = \Closure::bind(function($steamId) {
			$steamId->nickname = 'Icewind';
			$steamId->imageUrl = 'foo';
		}, null, $this->steamId);
		$closure($this->steamId);

		$this->provider = new UserProvider($this->getDatabaseConnection(), $this->getRandomGenerator());
	}

	public function testGetNonExisting() {
		$this->assertFalse($this->provider->get('76561198024494988'));
	}

	public function testStoreRetrieve() {
		$this->provider->store($this->steamId);

		$user = $this->provider->get('76561198024494988');

		$this->assertEquals($this->steamId->getNickname(), $user['name']);
		$this->assertEquals($this->steamId->getSteamId64(), '76561198024494988');
	}

	public function testDoubleInsert() {
		$this->provider->store($this->steamId);
		$this->provider->store($this->steamId);
	}

	public function testByKey() {
		$token = $this->provider->store($this->steamId);

		$byKey = $this->provider->byKey($token);
		$this->assertEquals('76561198024494988', $byKey['steamid']);
	}

	public function testSearch() {
		$result = $this->provider->search('__NOT__FOUND__');

		$this->assertCount(0, $result);
	}
}
