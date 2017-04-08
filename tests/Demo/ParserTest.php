<?php declare(strict_types=1);

namespace Demostf\API\Test\Providers;

use Demostf\API\Demo\Parser;
use Demostf\API\Demo\RawParser;
use Demostf\API\Test\TestCase;

class ParserTest extends TestCase {
	/** @var RawParser */
	private $rawParser;

	public function setUp() {
		parent::setUp();

		$this->rawParser = $this->getMockBuilder(RawParser::class)
			->disableOriginalConstructor()
			->getMock();

		$this->rawParser->expects($this->any())
			->method('parse')
			->will($this->returnCallback(function ($path) {
				$jsonPath = str_replace('.dem', '-raw.json', $path);
				return json_decode(file_get_contents($jsonPath), true);
			}));
	}

	public function testAnalyse() {
		$parser = new Parser($this->rawParser);

		$result = $parser->analyse(__DIR__ . '/../data/product.dem');

		$expected = json_decode(file_get_contents(__DIR__ . '/../data/product-analyse.json'), true);

		$this->assertEquals($expected, $result);
	}
}
