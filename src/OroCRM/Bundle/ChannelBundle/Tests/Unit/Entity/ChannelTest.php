<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Unit\Entity;

use Doctrine\Common\Collections\ArrayCollection;

use OroCRM\Bundle\ChannelBundle\Entity\Channel;

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
            'owner'        => ['owner', $owner, $owner],
        ];
    }

    public function testAddRemoveIntegrations()
    {
        $integration = $this->getMock('Oro\Bundle\IntegrationBundle\Entity\Channel');
        $collection = new ArrayCollection();
        $collection->add($integration);
        $channel = new Channel();
        $channel->addIntegrations($integration);

        $this->assertEquals(
            $channel->getIntegrations(),
            $collection
        );

        $collection->removeElement($integration);
        $channel->removeIntegrations($integration);

        $this->assertEquals(
            $channel->getIntegrations(),
            $collection
        );
    }
}
