<?php

namespace Oro\Bundle\MagentoBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\MagentoBundle\Entity\CreditMemo;
use Oro\Bundle\MagentoBundle\Provider\MagentoChannelType;

class AddCreditMemoConnectorsAndEntities extends AbstractFixture
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->updateConnectors($manager);
        $this->updateSalesChannelEntities($manager);
    }

    /**
     * @param ObjectManager $manager
     */
    protected function updateConnectors(ObjectManager $manager)
    {
        $channels = $manager
            ->getRepository('OroIntegrationBundle:Channel')
            ->findBy(['type' => MagentoChannelType::TYPE]);

        $newConnectors = ['credit_memo_initial', 'credit_memo'];
        foreach ($channels as $channel) {
            $connectors = $channel->getConnectors();
            foreach ($newConnectors as $newConnector) {
                $key = array_search($newConnector, $connectors, true);
                if ($key === false) {
                    $connectors[] = $newConnector;
                }
            }

            $channel->setConnectors($connectors);
        }
        $manager->flush($channels);
    }

    /**
     * @param ObjectManager $manager
     */
    protected function updateSalesChannelEntities(ObjectManager $manager)
    {
        $salesChannels = $manager
            ->getRepository('OroChannelBundle:Channel')
            ->findBy(['channelType' => MagentoChannelType::TYPE]);

        foreach ($salesChannels as $salesChannel) {
            $entities = $salesChannel->getEntities();
            $key = array_search(CreditMemo::class, $entities, true);
            if ($key === false) {
                $entities[] = CreditMemo::class;
            }
            $salesChannel->setEntities($entities);
        }
        $manager->flush($salesChannels);
    }
}
