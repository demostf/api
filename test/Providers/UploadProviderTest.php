<?php

declare(strict_types=1);

namespace Demostf\API\Test\Providers;

use DateTime;
use Demostf\API\Data\ParsedDemo;
use Demostf\API\Demo\Demo;
use Demostf\API\Demo\DemoSaver;
use Demostf\API\Demo\DemoStore;
use Demostf\API\Demo\Header;
use Demostf\API\Demo\HeaderParser;
use Demostf\API\Demo\Parser;
use Demostf\API\Demo\RawParser;
use Demostf\API\Error\InvalidKeyException;
use Demostf\API\Providers\ChatProvider;
use Demostf\API\Providers\DemoProvider;
use Demostf\API\Providers\PlayerProvider;
use Demostf\API\Providers\UploadProvider;
use Demostf\API\Providers\UserProvider;
use Demostf\API\Test\TestCase;
use Doctrine\DBAL\Connection;
use InvalidArgumentException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionException;

class UploadProviderTest extends TestCase {
    /** @var RawParser */
    private $rawParser;
    /** @var HeaderParser */
    private $headerParser;
    /** @var Parser */
    private $parser;
    /** @var DemoStore */
    private $demoStore;
    /** @var UserProvider */
    private $userProvider;
    /** @var DemoProvider */
    private $demoProvider;
    /** @var DemoSaver */
    private $demoSaver;
    /** @var UploadProvider */
    private $uploadProvider;
    /** @var string */
    private $tmpDir;

    /**
     * @throws ReflectionException
     */
    protected function setUp(): void {
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

        $targetDir = tempnam(sys_get_temp_dir(), 'dummy_target_');
        unlink($targetDir);
        mkdir($targetDir);
        $this->tmpDir = $targetDir;

        $this->headerParser = new HeaderParser();
        $this->parser = new Parser($this->rawParser);
        $this->demoStore = new DemoStore($targetDir, 'example.com');
        $this->userProvider = new UserProvider($this->getDatabaseConnection(), $this->getRandomGenerator());
        $this->demoProvider = new DemoProvider($this->getDatabaseConnection(), $this->userProvider);
        $this->demoSaver = new DemoSaver(
            new PlayerProvider($this->getDatabaseConnection()),
            new ChatProvider($this->getDatabaseConnection()),
            $this->userProvider,
            $this->demoProvider,
            $this->createMock(Connection::class)
        );

        $this->uploadProvider = new UploadProvider(
            $this->getDatabaseConnection(),
            'http://example.com',
            $this->headerParser,
            $this->parser,
            $this->demoStore,
            $this->userProvider,
            $this->demoProvider,
            $this->demoSaver,
            ''
        );
    }

    private function rmdirr($dir) {
        $it = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new RecursiveIteratorIterator(
            $it,
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }
        rmdir($dir);
    }

    protected function tearDown(): void {
        $this->rmdirr($this->tmpDir);

        parent::tearDown();
    }

    public function testValidateHeaderToSmall() {
        $this->assertEquals('Demos needs to be at least 1KB is size', $this->uploadProvider->validateHeader(
            12,
            new Header(
                'HL2DEMO',
                1,
                2,
                'Server',
                'Nick',
                'cp_gullywash',
                'tf',
                12,
                12,
                12,
                12
            )
        ));
    }

    public function testValidateHeaderToBig() {
        $this->assertEquals('Demos cant be more than 200MB in size', $this->uploadProvider->validateHeader(
            99999999999,
            new Header(
                'HL2DEMO',
                1,
                2,
                'Server',
                'Nick',
                'cp_gullywash',
                'tf',
                12,
                12,
                12,
                12
            )
        ));
    }

    public function testValidateHeaderToLong() {
        $this->assertEquals('Demos cant be longer than one and a half hour', $this->uploadProvider->validateHeader(
            9999,
            new Header(
                'HL2DEMO',
                1,
                2,
                'Server',
                'Nick',
                'cp_gullywash',
                'tf',
                999999,
                12,
                12,
                12
            )
        ));
    }

    public function testValidateParsedToShortNoRounds() {
        $this->assertEquals('Demos must be at least 5 minutes long', $this->uploadProvider->validateParsed(
            new Header(
                'HL2DEMO',
                1,
                2,
                'Server',
                'Nick',
                'cp_gullywash',
                'tf',
                60,
                12,
                12,
                12
            ),
            new ParsedDemo(0, 0, [], [], [])
        ));
    }

    public function testValidateParsedToShortRounds() {
        $this->assertNull($this->uploadProvider->validateParsed(
            new Header(
                'HL2DEMO',
                1,
                2,
                'Server',
                'Nick',
                'cp_gullywash',
                'tf',
                60,
                12,
                12,
                12
            ),
            new ParsedDemo(1, 0, [], [], [])
        ));
    }

    public function testUploadInvalidKey() {
        $this->expectException(InvalidKeyException::class);
        $this->uploadProvider->upload('dummy', 'RED', 'BLU', 'dummy', 'dummy', false);
    }

    public function testUploadNonDemo() {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Not an HL2 demo');

        file_put_contents($this->tmpDir . '/foo.dem', 'asd');

        $steamId = $this->getSteamId('123', 'a');
        $token = $this->userProvider->store($steamId, 'a');

        $this->uploadProvider->upload($token, 'RED', 'BLU', 'dummy', $this->tmpDir . '/foo.dem', false);
    }

    public function testUploadExisting() {
        $source = fopen(__DIR__ . '/../data/product.dem', 'r');
        file_put_contents($this->tmpDir . '/foo.dem', fread($source, 2048));
        fclose($source);
        $hash = md5_file($this->tmpDir . '/foo.dem');

        $id = $this->demoProvider->storeDemo(
            new Demo(
                0,
                'a',
                'b',
                'c',
                12,
                'n',
                'm',
                new DateTime(),
                'r',
                'b',
                1,
                2,
                2,
                1,
                $hash,
                'b',
                'p',
                null,
            ),
            'test',
            'test'
        );

        $steamId = $this->getSteamId('123', 'a');
        $token = $this->userProvider->store($steamId, 'a');

        $this->assertEquals(
            'STV available at: http://example.com/' . $id,
            $this->uploadProvider->upload($token, 'RED', 'BLU', 'dummy', $this->tmpDir . '/foo.dem', false)
        );
    }

    private function saveSteamId($steamId, $name) {
        $steamId = $this->getSteamId(Parser::convertSteamIdToCommunityId($steamId), $name);
        $this->userProvider->store($steamId, $name);
    }

    private function preloadNames() {
        // pre-save the names so we don't have to get them from steam
        $this->saveSteamId('[U:1:64229260]', 'Icewind');
        $this->saveSteamId('[U:1:115748435]', 'Foz');
        $this->saveSteamId('[U:1:115754284]', 'Deity');
        $this->saveSteamId('[U:1:92428736]', 'Kireek');
        $this->saveSteamId('[U:1:22958903]', 'Vinegar');
        $this->saveSteamId('[U:1:32061783]', 'Kimo');
        $this->saveSteamId('[U:1:67502510]', 'magikarp');
        $this->saveSteamId('[U:1:55128465]', 'Solar');
        $this->saveSteamId('[U:1:301587080]', 'ztreak');
        $this->saveSteamId('[U:1:22162172]', 'TheMasterOfDisaster');
        $this->saveSteamId('[U:1:13559571]', 'Sage');
        $this->saveSteamId('[U:1:71706948]', 'Sketis');
        $this->saveSteamId('[U:1:157204170]', 'Pyla');
        $this->saveSteamId('[U:1:30838206]', 'Heavy');
        $this->saveSteamId('[U:1:174774002]', 'Soldier');
        $this->saveSteamId('[U:1:92096346]', 'Fish');
        $this->saveSteamId('[U:1:143626373]', 'Pendulum');
        $this->saveSteamId('[U:1:30220936]', 'Jedi');
        $this->saveSteamId('[U:1:1104797071]', 'Katsu');
    }

    public function uploadProvider(): array {
        return [
            [__DIR__ . '/../data/product.dem', __DIR__ . '/../data/product-raw.json', 'koth_product_rc8', 0, 3, false],
            [__DIR__ . '/../data/product.dem', __DIR__ . '/../data/product-raw.json', 'koth_product_rc8', 0, 3, true],
        ];
    }

    /**
     * @dataProvider uploadProvider
     */
    public function testUpload(string $demo, string $parsed, string $map, int $blue, int $red, bool $private) {
        copy($demo, $this->tmpDir . '/foo.dem');
        copy($parsed, $this->tmpDir . '/foo-raw.json');

        $steamId = $this->getSteamId('123', 'a');
        $token = $this->userProvider->store($steamId, 'a');

        $this->preloadNames();

        $result = $this->uploadProvider->upload($token, 'RED', 'BLU', 'foodemo', $this->tmpDir . '/foo.dem', $private);
        $this->assertStringStartsWith('STV available at: http://example.com/', $result);

        $demoId = (int) substr($result, \strlen('STV available at: http://example.com/'));

        $demo = $this->demoProvider->get($demoId, true);

        $this->assertNotNull($demo);

        $this->assertEquals($map, $demo->getMap());
        $this->assertEquals($blue, $demo->getBlueScore());
        $this->assertEquals($red, $demo->getRedScore());

        $json = $demo->jsonSerialize();
        if ($private) {
            $this->assertEquals('', $json['url']);
            $this->assertEquals('', $json['backend']);
            $this->assertEquals('', $json['path']);
        } else {
            $this->assertEquals($demo->getUrl(), $json['url']);
            $this->assertEquals($demo->getBackend(), $json['backend']);
            $this->assertEquals($demo->getPath(), $json['path']);
        }
    }
}
