<?php declare(strict_types=1);

namespace Demostf\API\Test\Demo;

use Demostf\API\Demo\Header;
use Demostf\API\Demo\HeaderParser;
use Demostf\API\Test\TestCase;

class HeaderParserTest extends TestCase {
    public function testParseFile() {
        $parser = new HeaderParser();

        $expected = new Header(
            'HL2DEMO',
            3,
            24,
            'UGC Highlander Match',
            'SourceTV Demo',
            'koth_product_rc8',
            'tf',
            778.4849853515625,
            51899,
            25703,
            818263
        );
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

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testNonExisting() {
        $parser = new HeaderParser();
        $parser->parseHeader('/non/existing');
    }
}
