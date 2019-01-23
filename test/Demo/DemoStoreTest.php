<?php

declare(strict_types=1);

namespace Demostf\API\Test\Demo;

use Demostf\API\Demo\DemoStore;
use Demostf\API\Test\TestCase;

class DemoStoreTest extends TestCase {
    public function testStore() {
        $targetDir = tempnam(sys_get_temp_dir(), 'dummy_target_');
        unlink($targetDir);
        mkdir($targetDir);

        $demoStore = new DemoStore($targetDir, 'static.example.com');

        $file = tempnam(sys_get_temp_dir(), 'dummy_');
        file_put_contents($file, 'foobar');

        $storedDemo = $demoStore->store($file, 'foodemo.dem');

        $this->assertStringEndsWith('/foodemo.dem', $storedDemo->getUrl());
        $this->assertStringStartsWith('https://static.example.com/', $storedDemo->getUrl());
        $this->assertEquals('static', $storedDemo->getBackend());

        $this->assertStringEqualsFile($storedDemo->getPath(), 'foobar');
        unlink($storedDemo->getPath());
        rmdir(\dirname($storedDemo->getPath()));
        rmdir(\dirname($storedDemo->getPath(), 2));
        rmdir($targetDir);
    }
}
