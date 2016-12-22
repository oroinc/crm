<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Entity;

class EntityNameTest extends AbstractEntityTestCase
{
    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $name         = $this->getEntityFQCN();
        $this->entity = unserialize(sprintf('O:%d:"%s":0:{}', strlen($name), $name));
    }

    /**
     * {@inheritDoc}
     */
    public function getDataProvider()
    {
        $channel = $this->createMock('Oro\Bundle\ChannelBundle\Entity\Channel');
        $name   = 'testName';

        return [
            'name'   => ['name', $name, $name],
            'channel' => ['channel', $channel, $channel],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getEntityFQCN()
    {
        return 'Oro\Bundle\ChannelBundle\Entity\EntityName';
    }
}
