<?php

declare(strict_types=1);

namespace Demostf\API\Test\Controllers;

use Demostf\API\Controllers\DemoController;
use Demostf\API\Demo\ChatMessage;
use Demostf\API\Demo\Demo;
use Demostf\API\Demo\DemoStore;
use Demostf\API\Providers\ChatProvider;
use Demostf\API\Providers\DemoListProvider;
use Demostf\API\Providers\DemoProvider;
use \InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;

class DemoControllerTest extends ControllerTest {
    /** @var DemoStore|MockObject $demoStore */
    private $demoStore;
    /** @var DemoProvider|MockObject $demoProvider */
    private $demoProvider;
    /** @var ChatProvider|MockObject $chatProvider */
    private $chatProvider;
    /** @var DemoListProvider|MockObject $demoListProvider */
    private $demoListProvider;

    public function setUp(): void {
        parent::setUp();

        $this->demoStore = $this->createMock(DemoStore::class);
        $this->demoProvider = $this->createMock(DemoProvider::class);
        $this->chatProvider = $this->createMock(ChatProvider::class);
        $this->demoListProvider = $this->createMock(DemoListProvider::class);
    }

    private function getController(array $get = [], array $post = [], array $files = []) {
        return new DemoController(
            $this->getRequest($get, $post, $files),
            $this->getResponse(),
            $this->demoProvider,
            $this->chatProvider,
            $this->demoListProvider,
            $this->demoStore,
            'supersecretkey'
        );
    }

    public function testGetBasicList() {
        $controller = $this->getController();

        $this->demoListProvider->expects($this->once())
            ->method('listDemos')
            ->with(1, [], 'DESC')
            ->willReturn(['dummy']);

        $controller->listDemos();
        $this->assertResponseData(['dummy']);
    }

    public function testGetListPageASC() {
        $controller = $this->getController(['page' => '3', 'order' => 'ASC']);

        $this->demoListProvider->expects($this->once())
            ->method('listDemos')
            ->with(3, [], 'ASC')
            ->willReturn(['dummy']);

        $controller->listDemos();
        $this->assertResponseData(['dummy']);
    }

    public function testListFilterBackend() {
        $controller = $this->getController(['backend' => 'foo']);

        $this->demoListProvider->expects($this->once())
            ->method('listDemos')
            ->with(1, ['backend' => 'foo'], 'DESC')
            ->willReturn(['dummy']);

        $controller->listDemos();
        $this->assertResponseData(['dummy']);
    }

    public function testSetDemoUrlInvalidKey() {
        $controller = $this->getController([], [
            'hash' => 'foo',
            'backend' => 'bar',
            'path' => '/bar',
            'url' => 'http://bar/',
            'key' => 'invalid',
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid key");

        $controller->setDemoUrl('1');
    }

    public function testSetDemoUrlInvalidHash() {
        $controller = $this->getController([], [
            'hash' => 'invalidhash',
            'backend' => 'bar',
            'path' => '/bar',
            'url' => 'http://bar/',
            'key' => 'supersecretkey',
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid demo hash");

        $demo = $this->createConfiguredMock(Demo::class, [
            'getHash' => 'validhash',
        ]);

        $this->demoProvider
            ->expects($this->once())
            ->method('get')
            ->with(1, true)
            ->willReturn($demo);

        $controller->setDemoUrl('1');
    }

    public function testSetDemoUrlNonStatic() {
        $controller = $this->getController([], [
            'hash' => 'validhash',
            'backend' => 'bar',
            'path' => '/bar',
            'url' => 'http://bar/',
            'key' => 'supersecretkey',
        ]);

        $demo = $this->createConfiguredMock(Demo::class, [
            'getHash' => 'validhash',
            'getBackend' => 'foo',
        ]);

        $this->demoProvider->expects($this->once())
            ->method('get')
            ->with(1, true)
            ->willReturn($demo);

        $this->demoProvider->expects($this->once())
            ->method('setDemoUrl')
            ->with(1, 'bar', 'http://bar/', '/bar');

        $this->demoStore->expects($this->never())
            ->method('remove');

        $controller->setDemoUrl('1');
    }

    public function testSetDemoUrlStatic() {
        $controller = $this->getController([], [
            'hash' => 'validhash',
            'backend' => 'bar',
            'path' => '/bar',
            'url' => 'http://bar/',
            'key' => 'supersecretkey',
        ]);

        $demo = $this->createConfiguredMock(Demo::class, [
            'getHash' => 'validhash',
            'getBackend' => 'static',
        ]);

        $this->demoProvider->expects($this->once())
            ->method('get')
            ->with(1, true)
            ->willReturn($demo);

        $this->demoProvider->expects($this->once())
            ->method('setDemoUrl')
            ->with(1, 'bar', 'http://bar/', '/bar');

        $this->demoStore->expects($this->once())
            ->method('remove')
            ->with($demo);

        $controller->setDemoUrl('1');
    }

    public function testGetChat() {
        $controller = $this->getController();

        $this->chatProvider->expects($this->once())
            ->method('getChat')
            ->with(1)
            ->willReturn([
                new ChatMessage('foo', 1, 'bar'),
                new ChatMessage('foo2', 2, 'bar2'),
            ]);

        $controller->chat('1');

        $this->assertResponseData([
            ['user' => 'foo', 'time' => 1, 'message' => 'bar'],
            ['user' => 'foo2', 'time' => 2, 'message' => 'bar2'],
        ]);
    }
}
