<?php
namespace Oro\Bundle\AnalyticsBundle\Tests\Functional\Async;

use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\MessageQueueBundle\Test\Functional\MessageCollector;
use Oro\Bundle\MessageQueueBundle\Test\Functional\MessageQueueExtension;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Component\MessageQueue\Transport\Null\NullMessage;
use Oro\Component\MessageQueue\Transport\Null\NullSession;
use Oro\Bundle\AnalyticsBundle\Async\CalculateAllChannelsAnalyticsProcessor;
use Oro\Bundle\AnalyticsBundle\Async\Topics;
use Oro\Bundle\AnalyticsBundle\Tests\Functional\DataFixtures\LoadCustomerData;
use Oro\Bundle\ChannelBundle\Entity\Channel;

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
        $processor = $this->getContainer()->get('oro_analytics.async.calculate_all_channels_analytics_processor');

        $this->assertInstanceOf(CalculateAllChannelsAnalyticsProcessor::class, $processor);
    }

    public function testShouldSendCalculateAnalyticsMessageForEachChannel()
    {
        /** @var CalculateAllChannelsAnalyticsProcessor $processor */
        $processor = $this->getContainer()->get('oro_analytics.async.calculate_all_channels_analytics_processor');

        $processor->process(new NullMessage(), new NullSession());

        self::assertMessagesCount(Topics::CALCULATE_CHANNEL_ANALYTICS, 4);
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

        $processor->process(new NullMessage(), new NullSession());

        self::assertMessagesCount(Topics::CALCULATE_CHANNEL_ANALYTICS, 3);
    }

    /**
     * @return EntityManagerInterface
     */
    private function getEntityManager()
    {
        return self::getContainer()->get('doctrine.orm.entity_manager');
    }
}
