<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Entity;

use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Entity\EntityName;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;

class EntityNameTest extends \PHPUnit\Framework\TestCase
{
    use EntityTestCaseTrait;

    public function testProperties()
    {
        $properties = [
            'id'      => ['id', 1],
            'name'    => ['name', 'another name', false],
            'channel' => ['channel', $this->createMock(Channel::class)],
        ];

        $entity = new EntityName('initial name');
        self::assertPropertyAccessors($entity, $properties);
    }
}
