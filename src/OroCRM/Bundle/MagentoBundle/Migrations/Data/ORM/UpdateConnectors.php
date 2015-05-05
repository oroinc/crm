<?php

namespace OroCRM\Bundle\MagentoBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use OroCRM\Bundle\MagentoBundle\Provider\ChannelType;
use OroCRM\Bundle\MagentoBundle\Provider\Connector\DictionaryConnectorInterface;

class UpdateConnectors extends AbstractFixture
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        /** @var Channel[] $channels */
        $channels = $manager->getRepository('OroIntegrationBundle:Channel')->findBy(['type' => ChannelType::TYPE]);

        foreach ($channels as $channel) {
            $connectors = $channel->getConnectors();
            $key = array_search('region', $connectors, true);

            if ($key === false) {
                $connectors[] = 'region' . DictionaryConnectorInterface::DICTIONARY_CONNECTOR_SUFFIX;
            } else {
                $connectors[$key] = 'region' . DictionaryConnectorInterface::DICTIONARY_CONNECTOR_SUFFIX;
            }

            $channel->setConnectors($connectors);
        }

        $manager->flush($channels);
    }
}
