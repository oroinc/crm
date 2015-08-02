<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Form\EventListener;

use Symfony\Component\Form\FormEvents;

use OroCRM\Bundle\MagentoBundle\Form\EventListener\CartItemApiFormSubscriber;

class CartItemApiFormSubscriberTest extends \PHPUnit_Framework_TestCase
{
    /** @var CartItemApiFormSubscriber */
    protected $subscriber;

    protected function setUp()
    {
        $this->subscriber = new CartItemApiFormSubscriber();
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
     * @param $cartItem
     */
    public function testPreSet($cartItem)
    {
        $event = $this->getMockBuilder('Symfony\Component\Form\FormEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $event->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($cartItem));

        if (null !== $cartItem) {
            $cartItem->expects($this->once())
                ->method('setCreatedAt')
                ->with($this->isInstanceOf('\DateTime'));

            $cartItem->expects($this->once())
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
        $cartItem = $this->getMockBuilder('OroCRM\Bundle\MagentoBundle\Entity\CartItem')
            ->disableOriginalConstructor()
            ->getMock();

        return [
            'with data'     => [
                'cartItem' => $cartItem
            ],
            'with out data' => [
                'cartItem' => null
            ]
        ];
    }
}
