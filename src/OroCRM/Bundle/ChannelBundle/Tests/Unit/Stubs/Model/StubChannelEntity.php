<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Unit\Stubs\Model;

use OroCRM\Bundle\ChannelBundle\Model\ChannelAwareInterface;
use OroCRM\Bundle\ChannelBundle\Model\ChannelEntityTrait;

class StubChannelEntity implements ChannelAwareInterface
{
    use ChannelEntityTrait;
}
