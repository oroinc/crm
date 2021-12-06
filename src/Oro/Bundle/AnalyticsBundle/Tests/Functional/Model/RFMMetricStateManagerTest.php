<?php

namespace Oro\Bundle\AnalyticsBundle\Tests\Functional\Model;

use Oro\Bundle\AnalyticsBundle\Model\RFMAwareInterface;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class RFMMetricStateManagerTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient();

        if (!\class_exists('Oro\Bundle\MagentoBundle\OroMagentoBundle', false)) {
            self::markTestSkipped('There is not suitable channel data in the system.');
        }

        $this->loadFixtures(['Oro\Bundle\MagentoBundle\Tests\Functional\DataFixtures\LoadCustomerData']);
    }

    public function testResetChannelMetrics()
    {
        /** @var Channel $channel */
        $channel = $this->getReference('Channel.CustomerChannel');

        /** @var RFMAwareInterface[] $entities */
        $entities = $this->getContainer()
            ->get('oro_entity.doctrine_helper')
            ->getEntityRepository($channel->getCustomerIdentity())
            ->findBy(['dataChannel' => $channel->getId()]);
        $this->assertNotEmpty($entities);

        foreach ($entities as $entity) {
            $this->assertNotEmpty($entity->getRecency());
            $this->assertNotEmpty($entity->getFrequency());
            $this->assertNotEmpty($entity->getMonetary());
        }

        $this->getContainer()->get('oro_analytics.model.rfm_state_manager')->resetMetrics($channel);

        $em = $this->getContainer()
            ->get('oro_entity.doctrine_helper')
            ->getEntityManager($channel->getCustomerIdentity());

        foreach ($entities as $entity) {
            $em->refresh($entity);

            $this->assertEmpty($entity->getRecency());
            $this->assertEmpty($entity->getFrequency());
            $this->assertEmpty($entity->getMonetary());
        }
    }

    public function testResetMetrics()
    {
        /** @var RFMAwareInterface[] $entities */
        $entities = $this->getContainer()
            ->get('oro_entity.doctrine_helper')
            ->getEntityRepository('Oro\Bundle\MagentoBundle\Entity\Customer')
            ->findAll();
        $this->assertNotEmpty($entities);

        $this->getContainer()->get('oro_analytics.model.rfm_state_manager')->resetMetrics();

        $em = $this->getContainer()
            ->get('oro_entity.doctrine_helper')
            ->getEntityManager('Oro\Bundle\MagentoBundle\Entity\Customer');

        foreach ($entities as $entity) {
            $em->refresh($entity);

            $this->assertEmpty($entity->getRecency());
            $this->assertEmpty($entity->getFrequency());
            $this->assertEmpty($entity->getMonetary());
        }
    }
}
