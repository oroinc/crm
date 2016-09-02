<?php
namespace OroCRM\Bundle\AnalyticsBundle\Tests\Functional\Async;

use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\MessageQueueBundle\Test\Functional\MessageCollector;
use Oro\Bundle\MessageQueueBundle\Test\Functional\MessageQueueExtension;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Component\MessageQueue\Transport\Null\NullMessage;
use Oro\Component\MessageQueue\Transport\Null\NullSession;
use OroCRM\Bundle\AnalyticsBundle\Async\CalculateAllChannelsAnalyticsProcessor;
use OroCRM\Bundle\AnalyticsBundle\Async\Topics;
use OroCRM\Bundle\AnalyticsBundle\Tests\Functional\DataFixtures\LoadCustomerData;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;

/**
 * @dbIsolationPerTest
 */
class CalculateAllChannelsAnalyticsProcessorTest extends WebTestCase
{
    use MessageQueueExtension;

    protected function setUp()
    {
        parent::setUp();

        $this->initClient();
        $this->loadFixtures([LoadCustomerData::class]);
    }

    public function testCouldBeGetFromContainerAsService()
    {
        /** @var CalculateAllChannelsAnalyticsProcessor $processor */
        $processor = $this->getContainer()->get('orocrm_analytics.async.calculate_all_channels_analytics_processor');

        $this->assertInstanceOf(CalculateAllChannelsAnalyticsProcessor::class, $processor);
    }

    public function testShouldSendCalculateAnalyticsMessageForEachChannel()
    {
        /** @var CalculateAllChannelsAnalyticsProcessor $processor */
        $processor = $this->getContainer()->get('orocrm_analytics.async.calculate_all_channels_analytics_processor');

        $processor->process(new NullMessage(), new NullSession());

        $traces = $this->getMessageProducer()->getTopicSentMessages(Topics::CALCULATE_CHANNEL_ANALYTICS);

        self::assertCount(4, $traces);
    }

    public function testShouldSendCalculateAnalyticsMessageOnlyForActiveChannels()
    {
        /** @var Channel $channel */
        $channel = $this->getReference('Channel.CustomerChannel');
        $channel->setStatus(Channel::STATUS_INACTIVE);

        $this->getEntityManager()->persist($channel);
        $this->getEntityManager()->flush();

        /** @var CalculateAllChannelsAnalyticsProcessor $processor */
        $processor = $this->getContainer()->get('orocrm_analytics.async.calculate_all_channels_analytics_processor');

        $processor->process(new NullMessage(), new NullSession());

        $traces = $this->getMessageProducer()->getTopicSentMessages(Topics::CALCULATE_CHANNEL_ANALYTICS);

        self::assertCount(3, $traces);
    }

    /**
     * @return MessageCollector
     */
    private function getMessageProducer()
    {
        return self::getContainer()->get('oro_message_queue.message_producer');
    }

    /**
     * @return EntityManagerInterface
     */
    private function getEntityManager()
    {
        return self::getContainer()->get('doctrine.orm.entity_manager');
    }
}
