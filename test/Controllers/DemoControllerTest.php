<?php

declare(strict_types=1);

namespace Demostf\API\Test\Controllers;

use Demostf\API\Controllers\DemoController;
use Demostf\API\Providers\ChatProvider;
use Demostf\API\Providers\DemoListProvider;
use Demostf\API\Providers\DemoProvider;

class DemoControllerTest extends ControllerTest {
    /** @var DemoProvider|\PHPUnit_Framework_MockObject_MockObject $demoProvider */
    private $demoProvider;
    /** @var ChatProvider|\PHPUnit_Framework_MockObject_MockObject $chatProvider */
    private $chatProvider;
    /** @var DemoListProvider|\PHPUnit_Framework_MockObject_MockObject $demoListProvider */
    private $demoListProvider;

    public function setUp() {
        parent::setUp();

        $this->demoProvider = $this->createMock(DemoProvider::class);
        $this->chatProvider = $this->createMock(ChatProvider::class);
        $this->demoListProvider = $this->createMock(DemoListProvider::class);
    }

    public function testGetBasicList() {
        $controller = new DemoController(
            $this->getRequest(),
            $this->getResponse(),
            $this->demoProvider,
            $this->chatProvider,
            $this->demoListProvider,
            ''
        );

        $this->demoListProvider->expects($this->once())
            ->method('listDemos')
            ->with(1, [], 'DESC')
            ->willReturn(['dummy']);

        $controller->listDemos();
        $this->assertEquals('["dummy"]', $this->getResponseData());
    }

    public function testGetListPageASC() {
        $controller = new DemoController(
            $this->getRequest(['page' => '3', 'order' => 'ASC']),
            $this->getResponse(),
            $this->demoProvider,
            $this->chatProvider,
            $this->demoListProvider,
            ''
        );

        $this->demoListProvider->expects($this->once())
            ->method('listDemos')
            ->with(3, [], 'ASC')
            ->willReturn(['dummy']);

        $controller->listDemos();
        $this->assertEquals('["dummy"]', $this->getResponseData());
    }

    public function testListFilterBackend() {
        $controller = new DemoController(
            $this->getRequest(['backend' => 'foo']),
            $this->getResponse(),
            $this->demoProvider,
            $this->chatProvider,
            $this->demoListProvider,
            ''
        );

        $this->demoListProvider->expects($this->once())
            ->method('listDemos')
            ->with(1, ['backend' => 'foo'], 'DESC')
            ->willReturn(['dummy']);

        $controller->listDemos();
        $this->assertEquals('["dummy"]', $this->getResponseData());
    }
}
