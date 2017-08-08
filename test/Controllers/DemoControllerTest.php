<?php

declare(strict_types=1);

namespace Demostf\API\Test\Controllers;

use Demostf\API\Controllers\DemoController;
use Demostf\API\Demo\Demo;
use Demostf\API\Demo\DemoStore;
use Demostf\API\Providers\ChatProvider;
use Demostf\API\Providers\DemoListProvider;
use Demostf\API\Providers\DemoProvider;

class DemoControllerTest extends ControllerTest {
    /** @var DemoStore|\PHPUnit_Framework_MockObject_MockObject $demoStore */
    private $demoStore;
    /** @var DemoProvider|\PHPUnit_Framework_MockObject_MockObject $demoProvider */
    private $demoProvider;
    /** @var ChatProvider|\PHPUnit_Framework_MockObject_MockObject $chatProvider */
    private $chatProvider;
    /** @var DemoListProvider|\PHPUnit_Framework_MockObject_MockObject $demoListProvider */
    private $demoListProvider;

    public function setUp() {
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
        $this->assertEquals('["dummy"]', $this->getResponseData());
    }

    public function testGetListPageASC() {
        $controller = $this->getController(['page' => '3', 'order' => 'ASC']);

        $this->demoListProvider->expects($this->once())
            ->method('listDemos')
            ->with(3, [], 'ASC')
            ->willReturn(['dummy']);

        $controller->listDemos();
        $this->assertEquals('["dummy"]', $this->getResponseData());
    }

    public function testListFilterBackend() {
        $controller = $this->getController(['backend' => 'foo']);

        $this->demoListProvider->expects($this->once())
            ->method('listDemos')
            ->with(1, ['backend' => 'foo'], 'DESC')
            ->willReturn(['dummy']);

        $controller->listDemos();
        $this->assertEquals('["dummy"]', $this->getResponseData());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid key
     */
    public function testSetDemoUrlInvalidKey() {
        $controller = $this->getController([], [
            'hash' => 'foo',
            'backend' => 'bar',
            'path' => '/bar',
            'url' => 'http://bar/',
            'key' => 'invalid',
        ]);

        $controller->setDemoUrl('1');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid demo hash
     */
    public function testSetDemoUrlInvalidHash() {
        $controller = $this->getController([], [
            'hash' => 'invalidhash',
            'backend' => 'bar',
            'path' => '/bar',
            'url' => 'http://bar/',
            'key' => 'supersecretkey',
        ]);

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
}
