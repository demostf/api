<?php declare(strict_types=1);

namespace Demostf\API\Test\Data;

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

		$url = $demoStore->store($file, 'foodemo.dem');

		$this->assertStringEndsWith('/foodemo.dem', $url);
		$this->assertStringStartsWith('https://static.example.com/', $url);

		$subPath = str_replace('https://static.example.com/', '', $url);

		$this->assertStringEqualsFile($targetDir . '/' . $subPath, 'foobar');
		unlink($targetDir . '/' . $subPath);
		rmdir(dirname($targetDir . '/' . $subPath));
		rmdir(dirname($targetDir . '/' . $subPath, 2));
		rmdir($targetDir);
	}
}
