<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Stubs\Model;

use Oro\Bundle\ChannelBundle\Model\ChannelAwareInterface;
use Oro\Bundle\ChannelBundle\Model\ChannelEntityTrait;

class StubChannelEntity implements ChannelAwareInterface
{
    use ChannelEntityTrait;
}
