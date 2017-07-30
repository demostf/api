<?php

declare(strict_types=1);

namespace Demostf\API\Test\Data;

use Demostf\API\Data\DemoPlayer;
use Demostf\API\Test\TestCase;

class DemoPlayerTest extends TestCase {
    public function testParseSerialize() {
        $data = [
            'id' => 1,
            'user_id' => 2,
            'name' => 'foo',
            'team' => 'red',
            'class' => 'sniper',
            'steamid' => 'asd',
            'avatar' => 'asd.png',
            'kills' => 5,
            'assists' => 3,
            'deaths' => 7,
        ];

        $demoPlayer = DemoPlayer::fromRow($data);

        $this->assertEquals($data, $demoPlayer->jsonSerialize());
    }
}
