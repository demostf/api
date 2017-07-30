<?php declare(strict_types = 1);

namespace Demostf\API\Test\Providers;

use Demostf\API\Demo\ChatMessage;
use Demostf\API\Providers\ChatProvider;
use Demostf\API\Test\TestCase;

class ChatProviderTest extends TestCase {
    /** @var ChatProvider */
    private $provider;

    public function setUp() {
        parent::setUp();

        $this->provider = new ChatProvider($this->getDatabaseConnection());
    }

    public function testGetEmptyChat() {
        $this->assertCount(0, $this->provider->getChat(1));
    }

    public function testStoreRetrieve() {
        $message1 = new ChatMessage('foo', 2, 'bar');
        $message2 = new ChatMessage('foo2', 2, 'bar2');
        $message3 = new ChatMessage('foo2', 2, 'bar2');

        $this->provider->storeChatMessage(1, $message1);
        $this->provider->storeChatMessage(1, $message2);
        $this->provider->storeChatMessage(2, $message3);

        $result = $this->provider->getChat(1);
        sort($result);

        $this->assertCount(2, $result);
        $this->assertEquals($message1, $result[0]);
        $this->assertEquals($message2, $result[1]);
    }
}
