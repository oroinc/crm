<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Form\EventListener;

use Symfony\Component\Form\FormEvents;

use OroCRM\Bundle\MagentoBundle\Form\EventListener\OrderApiFormSubscriber;

class OrderApiFormSubscriberTest extends \PHPUnit_Framework_TestCase
{
    /** @var OrderApiFormSubscriber */
    protected $subscriber;

    protected function setUp()
    {
        $this->subscriber = new OrderApiFormSubscriber();
    }

    protected function tearDown()
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
        $order = $this->getMockBuilder('OroCRM\Bundle\MagentoBundle\Entity\Order')
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
