<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Form\EventListener;

use Oro\Bundle\MagentoBundle\Form\EventListener\OrderApiFormSubscriber;
use Symfony\Component\Form\FormEvents;

class OrderApiFormSubscriberTest extends \PHPUnit\Framework\TestCase
{
    /** @var OrderApiFormSubscriber */
    protected $subscriber;

    protected function setUp(): void
    {
        $this->subscriber = new OrderApiFormSubscriber();
    }

    protected function tearDown(): void
    {
        unset($this->subscriber);
    }

    public function testGetSubscribedEvents()
    {
        $expected = [
            FormEvents::PRE_SET_DATA => 'preSet'
        ];

        $this->assertEquals($expected, $this->subscriber->getSubscribedEvents());
    }

    /**
     * @dataProvider preSetProvider
     *
     * @param $order
     */
    public function testPreSet($order)
    {
        $event = $this->getMockBuilder('Symfony\Component\Form\FormEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $event->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($order));

        if (null !== $order) {
            $order->expects($this->once())
                ->method('setCreatedAt')
                ->with($this->isInstanceOf('\DateTime'));

            $order->expects($this->once())
                ->method('setUpdatedAt')
                ->with($this->isInstanceOf('\DateTime'));
        }

        $this->subscriber->preSet($event);
    }

    /**
     * @return array
     */
    public function preSetProvider()
    {
        $order = $this->getMockBuilder('Oro\Bundle\MagentoBundle\Entity\Order')
            ->disableOriginalConstructor()
            ->getMock();

        return [
            'with data'     => [
                'order' => $order
            ],
            'with out data' => [
                'order' => null
            ]
        ];
    }
}
