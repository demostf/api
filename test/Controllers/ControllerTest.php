<?php

declare(strict_types=1);

namespace Demostf\API\Test\Controllers;

use Demostf\API\Test\TestCase;
use flight\net\Request;
use flight\net\Response;
use flight\util\Collection;

abstract class ControllerTest extends TestCase {
    /** @var string */
    private $responseData;

    protected function getRequest(array $get = [], array $post = [], array $files = []): Request {
        /** @var Request $mock */
        $mock = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();

        $mock->query = new Collection($get);
        $mock->data = new Collection($post);
        $mock->files = new Collection($files);

        return $mock;
    }

    protected function getResponse() {
        /** @var Response|\PHPUnit_Framework_MockObject_MockObject $mock */
        $mock = $this->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->setMethods(['send'])
            ->getMock();

        $mock->expects($this->any())
            ->method('send')
            ->willReturnCallback(function () use ($mock) {
                $reflection = new \ReflectionClass($mock);
                $bodyProperty = $reflection->getProperty('body');
                $bodyProperty->setAccessible(true);

                $this->responseData = $bodyProperty->getValue($mock);
            });

        return $mock;
    }

    protected function getResponseData() {
        return $this->responseData;
    }

    protected function assertResponseData($expected) {
        if (!\is_string($expected)) {
            $expected = json_encode($expected);
        }

        $this->assertEquals($expected, $this->getResponseData());
    }
}
