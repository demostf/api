<?php declare(strict_types=1);

namespace Demostf\API\Test\Providers;

use Demostf\API\Data\ParsedDemo;
use Demostf\API\Demo\Demo;
use Demostf\API\Demo\DemoSaver;
use Demostf\API\Demo\DemoStore;
use Demostf\API\Demo\Header;
use Demostf\API\Demo\HeaderParser;
use Demostf\API\Demo\Parser;
use Demostf\API\Demo\RawParser;
use Demostf\API\Providers\ChatProvider;
use Demostf\API\Providers\DemoProvider;
use Demostf\API\Providers\KillProvider;
use Demostf\API\Providers\PlayerProvider;
use Demostf\API\Providers\UploadProvider;
use Demostf\API\Providers\UserProvider;
use Demostf\API\Test\TestCase;

class UploadProviderTest extends TestCase {
    /** @var RawParser */
    private $rawParser;
    /** @var HeaderParser */
    private $headerParser;
    /** @var Parser */
    private $parser;
    /** @var  DemoStore */
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

        $targetDir = tempnam(sys_get_temp_dir(), 'dummy_target_');
        unlink($targetDir);
        mkdir($targetDir);
        $this->tmpDir = $targetDir;

        $this->headerParser = new HeaderParser();
        $this->parser = new Parser($this->rawParser);
        $this->demoStore = new DemoStore($targetDir, 'example.com');
        $this->userProvider = new UserProvider($this->getDatabaseConnection(), $this->getRandomGenerator());
        $this->demoProvider = new DemoProvider($this->getDatabaseConnection());
        $this->demoSaver = new DemoSaver(
            new KillProvider($this->getDatabaseConnection()),
            new PlayerProvider($this->getDatabaseConnection()),
            new ChatProvider($this->getDatabaseConnection()),
            $this->userProvider,
            $this->demoProvider
        );

        $this->uploadProvider = new UploadProvider(
            $this->getDatabaseConnection(),
            'http://example.com',
            $this->headerParser,
            $this->parser,
            $this->demoStore,
            $this->userProvider,
            $this->demoProvider,
            $this->demoSaver
        );
    }

    private function rmdirr($dir) {
        $it = new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new \RecursiveIteratorIterator(
            $it,
            \RecursiveIteratorIterator::CHILD_FIRST
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

    public function tearDown() {
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
        $this->assertEquals('Demos cant be more than 100MB in size', $this->uploadProvider->validateHeader(
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
        $this->assertEquals('Demos cant be longer than one hour', $this->uploadProvider->validateHeader(
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
        $this->assertEquals(
            'Invalid key',
            $this->uploadProvider->upload('asdasd', 'RED', 'BLU', 'asdasd', 'asdas')
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Not an HL2 demo
     */
    public function testUploadNonDemo() {
        file_put_contents($this->tmpDir . '/foo.dem', 'asd');

        $steamId = $this->getSteamId('123', 'a');
        $token = $this->userProvider->store($steamId);

        $this->uploadProvider->upload($token, 'RED', 'BLU', 'asdasd', $this->tmpDir . '/foo.dem');
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
                new \DateTime(),
                'r',
                'b',
                1,
                2,
                2,
                1,
                $hash,
                'b',
                'p'
            ),
            'test',
            'test'
        );

        $steamId = $this->getSteamId('123', 'a');
        $token = $this->userProvider->store($steamId);

        $this->assertEquals(
            'STV available at: http://example.com/' . $id,
            $this->uploadProvider->upload($token, 'RED', 'BLU', 'asdasd', $this->tmpDir . '/foo.dem')
        );
    }

    private function saveSteamId($steamId, $name) {
        $steamId = $this->getSteamId($this->parser->convertSteamIdToCommunityId($steamId), $name);
        $this->userProvider->store($steamId);
    }

    public function testUpload() {
        copy(__DIR__ . '/../data/product.dem', $this->tmpDir . '/foo.dem');
        copy(__DIR__ . '/../data/product-raw.json', $this->tmpDir . '/foo-raw.json');

        $steamId = $this->getSteamId('123', 'a');
        $token = $this->userProvider->store($steamId);

        // pre-save the names so we dont have to get them from steam
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


        $result = $this->uploadProvider->upload($token, 'RED', 'BLU', 'foodemo', $this->tmpDir . '/foo.dem');
        $this->assertStringStartsWith('STV available at: http://example.com/', $result);

        $demoId = (int)substr($result, strlen('STV available at: http://example.com/'));

        $demo = $this->demoProvider->get($demoId, true);

        $this->assertNotNull($demo);

        $this->assertEquals('koth_product_rc8', $demo->getMap());
        $this->assertEquals(0, $demo->getBlueScore());
        $this->assertEquals(3, $demo->getRedScore());
    }
}
