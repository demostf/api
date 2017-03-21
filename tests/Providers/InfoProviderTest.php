<?php declare(strict_types = 1);
namespace Demostf\API\Test\Providers;

use Demostf\API\Providers\InfoProvider;
use Demostf\API\Test\TestCase;

class InfoProviderTest extends TestCase {
	/** @var InfoProvider */
	private $provider;

	public function setUp() {
		parent::setUp();
		$this->provider = new InfoProvider($this->getDatabaseConnection());
	}

	public function testGetStats() {

	}
}
