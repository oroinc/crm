<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Unit\Entity;

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
        $channel = $this->getMock('OroCRM\Bundle\ChannelBundle\Entity\Channel');
        $value   = 'testValue';

        return [
            'value'   => ['value', $value, $value],
            'channel' => ['channel', $channel, $channel],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getEntityFQCN()
    {
        return 'OroCRM\Bundle\ChannelBundle\Entity\EntityName';
    }
}
