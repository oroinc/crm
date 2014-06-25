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
        $owner       = $this->getMock('Oro\Bundle\OrganizationBundle\Entity\Organization');
        $name        = 'Some name';
        $description = 'Some description';
        $entities    = ['a', 'b', 'c'];
        $integration = ['d', 'e', 'f'];

        return [
            'name'        => ['name', $name, $name],
            'description' => ['description', $description, $description],
            'entities'    => ['entities', $entities, $entities],
            'integration' => ['integration', $integration, $integration],
            'owner'       => ['owner', $owner, $owner],
        ];
    }

}
