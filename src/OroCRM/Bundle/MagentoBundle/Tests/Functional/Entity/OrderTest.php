<?php
namespace OroCRM\Bundle\MagentoBundle\Tests\Functional\Entity;

use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Component\MessageQueue\Client\MessagePriority;
use Oro\Component\MessageQueue\Client\TraceableMessageProducer;
use OroCRM\Bundle\AnalyticsBundle\Async\Topics;
use OroCRM\Bundle\MagentoBundle\Entity\Order;
use OroCRM\Bundle\MagentoBundle\Tests\Functional\Fixture\LoadRFMOrderData;

/**
 * @dbIsolationPerTest
 */
class OrderTest extends WebTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->initClient();
        $this->loadFixtures([LoadRFMOrderData::class]);
        $this->getMessageProducer()->clearTraces();
    }

    public function testShouldScheduleAnalyticsCalculateWhenOrderSubtotalAmountIsChanged()
    {
        // test for code written on modern yaml programming language
        // magento_analytics_customer_calculate from process.yml

        /** @var Order $order */
        $order = $this->getReference('order_1');
        $order->setSubtotalAmount(1234);

        self::assertNotEmpty($order->getDataChannel());
        self::assertNotEmpty($order->getCustomer());

        $this->getMessageProducer()->clearTraces();

        $this->getEntityManager()->persist($order);
        $this->getEntityManager()->flush();

        $traces = $this->getMessageProducer()->getTopicTraces(Topics::CALCULATE_CHANNEL_ANALYTICS);
        self::assertCount(1, $traces);
        self::assertEquals([
            'channel_id' => $order->getDataChannel()->getId(),
            'customer_ids' => [$order->getCustomer()->getId()],
        ], $traces[0]['message']);
        self::assertEquals(MessagePriority::VERY_LOW, $traces[0]['priority']);
    }

    /**
     * @return EntityManagerInterface
     */
    private function getEntityManager()
    {
        return self::getContainer()->get('doctrine.orm.entity_manager');
    }

    /**
     * @return TraceableMessageProducer
     */
    private function getMessageProducer()
    {
        return self::getContainer()->get('oro_message_queue.message_producer');
    }
}
