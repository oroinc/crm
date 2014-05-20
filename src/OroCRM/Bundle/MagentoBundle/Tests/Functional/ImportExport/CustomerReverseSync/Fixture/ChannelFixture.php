<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Functional\ImportExport\CustomerReverseSync\Fixture;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;

use Oro\Bundle\IntegrationBundle\Entity\Channel;

class ChannelFixture extends AbstractFixture
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $transports = $manager->getRepository('OroCRMMagentoBundle:MagentoSoapTransport')->findAll();
        $channel    = new Channel();

        $channel->setName('Demo Web store');
        $channel->setType('magento');
        $channel->setConnectors(["customer", "order", "cart", "region"]);
        $channel->setTransport(reset($transports));

        $manager->persist($channel);
        $manager->flush();
    }
}
