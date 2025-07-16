<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Model;

use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Model\ChannelEntityTrait;
use PHPUnit\Framework\TestCase;

class ChannelEntityTraitTest extends TestCase
{
    public function testPropertyAndAccessors(): void
    {
        $stub = new class() {
            use ChannelEntityTrait;
        };

        self::assertNull($stub->getDataChannel());

        $channel = $this->createMock(Channel::class);
        $stub->setDataChannel($channel);

        self::assertSame($channel, $stub->getDataChannel());
    }
}
