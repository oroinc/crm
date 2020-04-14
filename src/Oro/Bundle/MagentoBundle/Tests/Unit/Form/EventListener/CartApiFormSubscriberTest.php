<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\Form\EventListener;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\MagentoBundle\Form\EventListener\CartApiFormSubscriber;
use Symfony\Component\Form\FormEvents;

class CartApiFormSubscriberTest extends \PHPUnit\Framework\TestCase
{
    /** @var CartApiFormSubscriber */
    protected $subscriber;

    protected function setUp(): void
    {
        $this->subscriber = new CartApiFormSubscriber();
    }

    protected function tearDown(): void
    {
        unset($this->subscriber);
    }

    public function testGetSubscribedEvents()
    {
        $expected = [
            FormEvents::PRE_SET_DATA => 'preSet',
            FormEvents::SUBMIT       => 'submit'
        ];

        $this->assertEquals($expected, $this->subscriber->getSubscribedEvents());
    }

    /**
     * @dataProvider preSetProvider
     *
     * @param  $cart
     */
    public function testPreSet($cart)
    {
        $event = $this->getMockBuilder('Symfony\Component\Form\FormEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $event->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($cart));

        if (null !== $cart) {
            $cart->expects($this->once())
                ->method('setCreatedAt')
                ->with($this->isInstanceOf('\DateTime'));

            $cart->expects($this->once())
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
        $cart = $this->getMockBuilder('Oro\Bundle\MagentoBundle\Entity\Cart')
            ->disableOriginalConstructor()
            ->getMock();

        return [
            'with data'     => [
                'cart' => $cart
            ],
            'with out data' => [
                'cart' => null
            ]
        ];
    }

    public function testSubmit()
    {
        $event = $this->getMockBuilder('Symfony\Component\Form\FormEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $cart = $this->getMockBuilder('Oro\Bundle\MagentoBundle\Entity\Cart')
            ->setMethods(['getCartItems'])
            ->disableOriginalConstructor()
            ->getMock();

        $cartItem = $this->getMockBuilder('Oro\Bundle\MagentoBundle\Entity\CartItem')
            ->disableOriginalConstructor()
            ->getMock();

        $cart->expects($this->any())
            ->method('getCartItems')
            ->will($this->returnValue(new ArrayCollection([$cartItem])));

        $event->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($cart));

        $this->subscriber->submit($event);
    }
}
