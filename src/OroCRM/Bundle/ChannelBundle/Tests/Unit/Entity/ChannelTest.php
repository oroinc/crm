<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Unit\Entity;

class ChannelTest extends AbstractEntityTestCase
{
    /**
     * {@inheritDoc}
     */
    public function getEntityFQCN()
    {
        return 'OroCRM\Bundle\ChannelBundle\Entity\Channel';
    }

    /**
     * {@inheritDoc}
     */
    public function getDataProvider()
    {
        $name         = 'Some name';
        $description  = 'Some description';
        $entities     = ['a', 'b', 'c'];
        $integrations = ['d', 'e', 'f'];
        $owner        = $this->getMock('Oro\Bundle\OrganizationBundle\Entity\Organization');
        
        return [
            'name'         => ['name', $name, $name],
            'description'  => ['description', $description, $description],
            'entities'     => ['entities', $entities, $entities],
            'integrations' => ['integrations', $integrations, $integrations],
            'owner'        => ['owner', $owner, $owner],
        ];
    }
}
