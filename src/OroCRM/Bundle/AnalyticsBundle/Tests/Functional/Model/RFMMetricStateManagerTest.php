<?php

namespace OroCRM\Bundle\AnalyticsBundle\Tests\Functional\Model;

use JMS\JobQueueBundle\Entity\Job;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use OroCRM\Bundle\AnalyticsBundle\Command\CalculateAnalyticsCommand;
use OroCRM\Bundle\AnalyticsBundle\Model\RFMAwareInterface;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;

/**
 * @dbIsolation
 */
class RFMMetricStateManagerTest extends WebTestCase
{
    protected function setUp()
    {
        $this->initClient();

        $this->loadFixtures(['OroCRM\Bundle\AnalyticsBundle\Tests\Functional\DataFixtures\LoadEntitiesData']);
    }

    public function tearDown()
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $entities = $em
            ->getRepository('JMS\JobQueueBundle\Entity\Job')
            ->findAll();

        foreach ($entities as $entity) {
            $em->remove($entity);
        }

        $em->flush();
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

        $this->getContainer()->get('orocrm_analytics.model.rfm_state_manager')->resetMetrics($channel);

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
            ->getEntityRepository('OroCRM\Bundle\MagentoBundle\Entity\Customer')
            ->findAll();
        $this->assertNotEmpty($entities);

        $this->getContainer()->get('orocrm_analytics.model.rfm_state_manager')->resetMetrics();

        $em = $this->getContainer()
            ->get('oro_entity.doctrine_helper')
            ->getEntityManager('OroCRM\Bundle\MagentoBundle\Entity\Customer');

        foreach ($entities as $entity) {
            $em->refresh($entity);

            $this->assertEmpty($entity->getRecency());
            $this->assertEmpty($entity->getFrequency());
            $this->assertEmpty($entity->getMonetary());
        }
    }

    public function testScheduleShouldAddJob()
    {
        /** @var Job[] $entities */
        $entities = $this->getContainer()
            ->get('oro_entity.doctrine_helper')
            ->getEntityRepository('JMS\JobQueueBundle\Entity\Job')
            ->findBy(['command' => CalculateAnalyticsCommand::COMMAND_NAME]);

        $this->assertEmpty($entities);

        $this->getContainer()->get('orocrm_analytics.model.rfm_state_manager')->scheduleRecalculation();
        $this->getContainer()->get('orocrm_analytics.model.rfm_state_manager')->scheduleRecalculation();

        /** @var Job[] $entities */
        $entities = $this->getContainer()
            ->get('oro_entity.doctrine_helper')
            ->getEntityRepository('JMS\JobQueueBundle\Entity\Job')
            ->findBy(['command' => CalculateAnalyticsCommand::COMMAND_NAME]);

        $this->assertCount(1, $entities);
    }

    public function testScheduleChannelShouldNotAddJobIfInactive()
    {
        /** @var Channel $channel */
        $channel = $this->getReference('Channel.AnalyticsAwareInterface');
        $this->getContainer()->get('orocrm_analytics.model.rfm_state_manager')->scheduleRecalculation($channel);

        /** @var Job[] $entities */
        $entities = $this->getContainer()
            ->get('oro_entity.doctrine_helper')
            ->getEntityRepository('JMS\JobQueueBundle\Entity\Job')
            ->findBy(['command' => CalculateAnalyticsCommand::COMMAND_NAME]);

        $this->assertCount(0, $entities);
    }

    public function testScheduleChannelShouldNotAddJobIfRFMDIsabled()
    {
        /** @var Channel $channel */
        $channel = $this->getReference('Channel.CustomerChannel3');
        $this->getContainer()->get('orocrm_analytics.model.rfm_state_manager')->scheduleRecalculation($channel);

        /** @var Job[] $entities */
        $entities = $this->getContainer()
            ->get('oro_entity.doctrine_helper')
            ->getEntityRepository('JMS\JobQueueBundle\Entity\Job')
            ->findBy(['command' => CalculateAnalyticsCommand::COMMAND_NAME]);

        $this->assertCount(0, $entities);
    }

    public function testScheduleChannelShouldNotAddJobIfGenericExists()
    {
        $this->getContainer()->get('orocrm_analytics.model.rfm_state_manager')->scheduleRecalculation();

        /** @var Channel $channel */
        $channel = $this->getReference('Channel.CustomerChannel');

        /** @var Job[] $entities */
        $entities = $this->getContainer()
            ->get('oro_entity.doctrine_helper')
            ->getEntityRepository('JMS\JobQueueBundle\Entity\Job')
            ->findBy(['command' => CalculateAnalyticsCommand::COMMAND_NAME]);

        $this->assertCount(1, $entities);

        $this->getContainer()->get('orocrm_analytics.model.rfm_state_manager')->scheduleRecalculation($channel);

        /** @var Job[] $entities */
        $entities = $this->getContainer()
            ->get('oro_entity.doctrine_helper')
            ->getEntityRepository('JMS\JobQueueBundle\Entity\Job')
            ->findBy(['command' => CalculateAnalyticsCommand::COMMAND_NAME]);

        foreach ($entities as $entity) {
            $this->assertEmpty($entity->getArgs());
        }

        $this->assertCount(1, $entities);
    }

    public function testScheduleShouldAddGenericJobAndDropChannelJobExists()
    {
        /** @var Channel $channel */
        $channel = $this->getReference('Channel.CustomerChannel');
        $this->getContainer()->get('orocrm_analytics.model.rfm_state_manager')->scheduleRecalculation($channel);

        /** @var Channel $channel2 */
        $channel2 = $this->getReference('Channel.CustomerChannel2');
        $this->getContainer()->get('orocrm_analytics.model.rfm_state_manager')->scheduleRecalculation($channel2);

        /** @var Job[] $entities */
        $entities = $this->getContainer()
            ->get('oro_entity.doctrine_helper')
            ->getEntityRepository('JMS\JobQueueBundle\Entity\Job')
            ->findBy(['command' => CalculateAnalyticsCommand::COMMAND_NAME]);

        foreach ($entities as $entity) {
            $this->assertNotEmpty($entity->getArgs());
        }

        $this->assertCount(2, $entities);

        $this->getContainer()->get('orocrm_analytics.model.rfm_state_manager')->scheduleRecalculation();

        /** @var Job[] $entities */
        $entities = $this->getContainer()
            ->get('oro_entity.doctrine_helper')
            ->getEntityRepository('JMS\JobQueueBundle\Entity\Job')
            ->findBy(['command' => CalculateAnalyticsCommand::COMMAND_NAME]);

        $this->assertCount(1, $entities);

        foreach ($entities as $entity) {
            $this->assertEmpty($entity->getArgs());
        }
    }

    public function testScheduleDifferentChannels()
    {
        /** @var Channel $channel */
        $channel = $this->getReference('Channel.CustomerChannel');
        $this->getContainer()->get('orocrm_analytics.model.rfm_state_manager')->scheduleRecalculation($channel);

        /** @var Channel $channel2 */
        $channel2 = $this->getReference('Channel.CustomerChannel2');
        $this->getContainer()->get('orocrm_analytics.model.rfm_state_manager')->scheduleRecalculation($channel2);

        /** @var Job[] $entities */
        $entities = $this->getContainer()
            ->get('oro_entity.doctrine_helper')
            ->getEntityRepository('JMS\JobQueueBundle\Entity\Job')
            ->findBy(['command' => CalculateAnalyticsCommand::COMMAND_NAME]);

        foreach ($entities as $entity) {
            $this->assertNotEmpty($entity->getArgs());
        }

        $this->assertCount(2, $entities);
    }

    public function testScheduleSameChannel()
    {
        /** @var Channel $channel */
        $channel = $this->getReference('Channel.CustomerChannel');
        $this->getContainer()->get('orocrm_analytics.model.rfm_state_manager')->scheduleRecalculation($channel);
        $this->getContainer()->get('orocrm_analytics.model.rfm_state_manager')->scheduleRecalculation($channel);

        /** @var Job[] $entities */
        $entities = $this->getContainer()
            ->get('oro_entity.doctrine_helper')
            ->getEntityRepository('JMS\JobQueueBundle\Entity\Job')
            ->findBy(['command' => CalculateAnalyticsCommand::COMMAND_NAME]);

        foreach ($entities as $entity) {
            $this->assertNotEmpty($entity->getArgs());
        }

        $this->assertCount(1, $entities);
    }
}
