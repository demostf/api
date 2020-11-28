<?php

declare(strict_types=1);

namespace Demostf\API\Test\Demo;

use Demostf\API\Demo\Parser;
use Demostf\API\Demo\RawParser;
use Demostf\API\Test\TestCase;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;

class ParserTest extends TestCase {
    /** @var RawParser|MockObject */
    private $rawParser;

    public function setUp(): void {
        parent::setUp();

        $this->rawParser = $this->getMockBuilder(RawParser::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->rawParser->expects($this->any())
            ->method('parse')
            ->willReturnCallback(function ($path) {
                $jsonPath = str_replace('.dem', '-raw.json', $path);

                return json_decode(file_get_contents($jsonPath), true);
            });
    }

    public function testAnalyse() {
        $parser = new Parser($this->rawParser);

        $result = $parser->analyse(__DIR__ . '/../data/product.dem');

        $expectedRaw = json_decode(file_get_contents(__DIR__ . '/../data/product-analyse.json'), true);

        $expectedChat = $expectedRaw['chat'];
        $this->assertCount(\count($expectedChat), $result->getChat());
        $this->assertEquals($expectedChat[0]['text'], $result->getChat()[0]->getMessage());
        $this->assertEquals($expectedChat[0]['time'], $result->getChat()[0]->getTime());
        $this->assertEquals($expectedChat[0]['from'], $result->getChat()[0]->getUser());

        $this->assertEquals($expectedRaw['score']['red'], $result->getRedScore());
        $this->assertEquals($expectedRaw['score']['blue'], $result->getBlueScore());

        $expectedPlayers = $expectedRaw['players'];
        $this->assertCount(\count($expectedPlayers), $result->getPlayers());
        $this->assertEquals($expectedPlayers[0]['name'], $result->getPlayers()[0]->getName());
        $this->assertEquals($expectedPlayers[0]['demo_user_id'], $result->getPlayers()[0]->getDemoUserId());
        $this->assertEquals($expectedPlayers[0]['team'], $result->getPlayers()[0]->getTeam());
        $this->assertEquals($expectedPlayers[0]['class'], $result->getPlayers()[0]->getClass());
        $this->assertEquals(Parser::convertSteamIdToCommunityId($expectedPlayers[0]['steam_id']), $result->getPlayers()[0]->getSteamId());

        $expectedKills = $expectedRaw['kills'];
        $this->assertCount(\count($expectedKills), $result->getKills());
        $this->assertEquals((int) $expectedKills[0]['killer'], $result->getKills()[0]->getAttackerDemoId());
        $this->assertEquals((int) $expectedKills[0]['assister'], $result->getKills()[0]->getAssisterDemoId());
        $this->assertEquals((int) $expectedKills[0]['victim'], $result->getKills()[0]->getVictimDemoId());
        $this->assertEquals($expectedKills[0]['weapon'], $result->getKills()[0]->getWeapon());
    }

    public function testFailedParse() {
        $this->expectException(InvalidArgumentException::class);

        /** @var RawParser|\PHPUnit_Framework_MockObject_MockObject $rawParser */
        $rawParser = $this->getMockBuilder(RawParser::class)
            ->disableOriginalConstructor()
            ->getMock();

        $parser = new Parser($rawParser);

        $rawParser->expects($this->any())
            ->method('parse')
            ->willReturn(null);

        $parser->analyse('foo');
    }

    public function testConvertSteamIdToCommunityId() {
        $steamId64 = Parser::convertSteamIdToCommunityId('STEAM_0:0:12345');
        $this->assertEquals('76561197960290418', $steamId64);
    }

    public function testConvertUIdToCommunityId() {
        $steamId64 = Parser::convertSteamIdToCommunityId('[U:1:12345]');
        $this->assertEquals('76561197960278073', $steamId64);
        $steamId64 = Parser::convertSteamIdToCommunityId('[U:1:39743963]');
        $this->assertEquals('76561198000009691', $steamId64);
    }
}
