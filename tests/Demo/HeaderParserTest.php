<?php declare(strict_types=1);

namespace Demostf\API\Test\Providers;

use Demostf\API\Demo\Header;
use Demostf\API\Demo\HeaderParser;
use Demostf\API\Test\TestCase;

class HeaderParserTest extends TestCase {
	public function testParseFile() {
		$parser = new HeaderParser();

		$expected = new Header([
			'type' => 'HL2DEMO',
			'version' => 3,
			'protocol' => 24,
			'server' => 'UGC Highlander Match',
			'nick' => 'SourceTV Demo',
			'map' => 'koth_product_rc8',
			'game' => 'tf',
			'duration' => 778.4849853515625,
			'ticks' => 51899,
			'frames' => 25703,
			'sigon' => 818263
		]);
		$parsed = $parser->parseHeader(__DIR__ . '/../data/product.dem');

		$this->assertEquals($expected->getServer(), $parsed->getServer());
		$this->assertEquals($expected->getDuration(), $parsed->getDuration());
		$this->assertEquals($expected->getTicks(), $parsed->getTicks());
		$this->assertEquals($expected->getFrames(), $parsed->getFrames());
		$this->assertEquals($expected->getGame(), $parsed->getGame());
		$this->assertEquals($expected->getMap(), $parsed->getMap());
		$this->assertEquals($expected->getNick(), $parsed->getNick());
		$this->assertEquals($expected->getProtocol(), $parsed->getProtocol());
		$this->assertEquals($expected->getSigon(), $parsed->getSigon());
		$this->assertEquals($expected->getType(), $parsed->getType());
		$this->assertEquals($expected->getVersion(), $parsed->getVersion());
	}

	/**
	 * @expectedException \InvalidArgumentException
	 * @expectedExceptionMessage Not an HL2 demo
	 */
	public function testNonDemoShort() {
		$parser = new HeaderParser();
		$parser->parseString("short");
	}

	/**
	 * @expectedException \InvalidArgumentException
	 * @expectedExceptionMessage Not an HL2 demo
	 */
	public function testNonDemoLong() {
		$parser = new HeaderParser();
		$parser->parseHeader(__FILE__);
	}
}
