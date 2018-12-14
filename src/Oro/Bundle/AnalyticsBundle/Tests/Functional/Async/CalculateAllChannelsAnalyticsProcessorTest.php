<?php

namespace Oro\Bundle\AnalyticsBundle\Tests\Functional\Async;

use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\AnalyticsBundle\Async\CalculateAllChannelsAnalyticsProcessor;
use Oro\Bundle\AnalyticsBundle\Async\Topics;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\MessageQueueBundle\Test\Functional\MessageQueueExtension;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Component\MessageQueue\Transport\ConnectionInterface;
use Oro\Component\MessageQueue\Transport\Message;

/**
 * @group crm
 *
 * @dbIsolationPerTest
 */
class CalculateAllChannelsAnalyticsProcessorTest extends WebTestCase
{
    use MessageQueueExtension;

    protected function setUp(): void
    {
        parent::setUp();

        $this->initClient();

        if (!\class_exists('Oro\Bundle\MagentoBundle\OroMagentoBundle', false)) {
            static::markTestSkipped('There is no suitable channel data in the system.');
            return;
        }

        $this->loadFixtures(['Oro\Bundle\MagentoBundle\Tests\Functional\DataFixtures\LoadCustomerData']);
    }

    public function testCouldBeGetFromContainerAsService()
    {
        /** @var CalculateAllChannelsAnalyticsProcessor $processor */
        $processor = $this->getContainer()->get('oro_analytics.async.calculate_all_channels_analytics_processor');

        $this->assertInstanceOf(CalculateAllChannelsAnalyticsProcessor::class, $processor);
    }

    public function testShouldSendCalculateAnalyticsMessageForEachChannel()
    {
        /** @var CalculateAllChannelsAnalyticsProcessor $processor */
        $processor = $this->getContainer()->get('oro_analytics.async.calculate_all_channels_analytics_processor');
        /** @var ConnectionInterface $connection */
        $connection = $this->getContainer()->get('oro_message_queue.transport.connection');

        $processor->process(new Message(), $connection->createSession());

        self::assertMessagesCount(Topics::CALCULATE_CHANNEL_ANALYTICS, 3);
    }

    public function testShouldSendCalculateAnalyticsMessageOnlyForActiveChannels()
    {
        /** @var Channel $channel */
        $channel = $this->getReference('Channel.CustomerChannel');
        $channel->setStatus(Channel::STATUS_INACTIVE);

        $this->getEntityManager()->persist($channel);
        $this->getEntityManager()->flush();

        /** @var CalculateAllChannelsAnalyticsProcessor $processor */
        $processor = $this->getContainer()->get('oro_analytics.async.calculate_all_channels_analytics_processor');
        /** @var ConnectionInterface $connection */
        $connection = $this->getContainer()->get('oro_message_queue.transport.connection');

        $processor->process(new Message(), $connection->createSession());

        self::assertMessagesCount(Topics::CALCULATE_CHANNEL_ANALYTICS, 2);
    }

    /**
     * @return EntityManagerInterface
     */
    protected function getEntityManager()
    {
        return self::getContainer()->get('doctrine.orm.entity_manager');
    }
}
