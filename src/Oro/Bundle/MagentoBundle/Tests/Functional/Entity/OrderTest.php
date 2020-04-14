<?php
namespace Oro\Bundle\MagentoBundle\Tests\Functional\Entity;

use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\AnalyticsBundle\Async\Topics;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\MagentoBundle\Entity\Order;
use Oro\Bundle\MagentoBundle\Tests\Functional\Fixture\LoadRFMOrderData;
use Oro\Bundle\MessageQueueBundle\Test\Functional\MessageQueueExtension;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Component\MessageQueue\Client\MessagePriority;

/**
 * @dbIsolationPerTest
 */
class OrderTest extends WebTestCase
{
    use MessageQueueExtension;

    protected function setUp(): void
    {
        parent::setUp();

        $this->initClient();
        $this->loadFixtures([LoadRFMOrderData::class]);
    }

    /**
     * test for magento_analytics_customer_calculate process
     */
    public function testShouldScheduleAnalyticsCalculateWhenOrderSubtotalAmountIsChanged()
    {
        /** @var Order $order */
        $order = $this->getReference('order_1');
        $channel = $order->getDataChannel();
        $order->setSubtotalAmount(1234);

        self::assertInstanceOf(Channel::class, $channel);
        self::assertNotEmpty($order->getCustomer());

        $channel->setStatus(Channel::STATUS_ACTIVE);
        $channel->setData(['rfm_enabled' => true]);

        self::getMessageCollector()->clear();

        $this->getEntityManager()->persist($channel);
        $this->getEntityManager()->persist($order);
        $this->getEntityManager()->flush();

        $traces = self::getMessageCollector()->getTopicSentMessages(Topics::CALCULATE_CHANNEL_ANALYTICS);
        self::assertCount(1, $traces);
        self::assertEquals([
            'channel_id' => $order->getDataChannel()->getId(),
            'customer_ids' => [$order->getCustomer()->getId()],
        ], $traces[0]['message']->getBody());
        self::assertEquals(MessagePriority::VERY_LOW, $traces[0]['message']->getPriority());
    }

    /**
     * @return EntityManagerInterface
     */
    private function getEntityManager()
    {
        return self::getContainer()->get('doctrine.orm.entity_manager');
    }
}
