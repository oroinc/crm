<?php

namespace Oro\Bundle\AnalyticsBundle\Tests\Functional\Model;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\AnalyticsBundle\Model\RFMAwareInterface;
use Oro\Bundle\AnalyticsBundle\Tests\Functional\DataFixtures\LoadCustomerData;
use Oro\Bundle\ChannelBundle\Entity\Channel;

/**
 * @dbIsolation
 */
class RFMMetricStateManagerTest extends WebTestCase
{
    protected function setUp()
    {
        $this->initClient();

        $this->loadFixtures([LoadCustomerData::class]);
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

    /**
     * @param object $entity
     *
     * @return EntityManager
     */
    protected function getManager($entity)
    {
        return $this->getContainer()
            ->get('oro_entity.doctrine_helper')
            ->getEntityManager($entity);
    }
}
