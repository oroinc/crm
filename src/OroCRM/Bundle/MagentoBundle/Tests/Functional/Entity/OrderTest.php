<?php
namespace OroCRM\Bundle\MagentoBundle\Tests\Functional\Entity;

use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\MessageQueueBundle\Test\Functional\MessageQueueExtension;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Component\MessageQueue\Client\MessagePriority;
use OroCRM\Bundle\AnalyticsBundle\Async\Topics;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\MagentoBundle\Entity\Order;
use OroCRM\Bundle\MagentoBundle\Tests\Functional\Fixture\LoadRFMOrderData;

/**
 * @dbIsolationPerTest
 */
class OrderTest extends WebTestCase
{
    use MessageQueueExtension;

    public function setUp()
    {
        parent::setUp();

        $this->initClient();
        $this->loadFixtures([LoadRFMOrderData::class]);
    }

    public function testShouldScheduleAnalyticsCalculateWhenOrderSubtotalAmountIsChanged()
    {
        // test for code written on modern yaml programming language
        // magento_analytics_customer_calculate from process.yml

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
