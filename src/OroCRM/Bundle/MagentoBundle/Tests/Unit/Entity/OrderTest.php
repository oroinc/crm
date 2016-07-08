<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Unit\Entity;

use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\IntegrationBundle\Entity\Channel;

use OroCRM\Bundle\MagentoBundle\Entity\Cart;
use OroCRM\Bundle\MagentoBundle\Entity\Order;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Entity\OrderItem;
use OroCRM\Bundle\MagentoBundle\Entity\Store;

class OrderTest extends AbstractEntityTestCase
{
    /** @var Order */
    protected $entity;

    /**
     * {@inheritDoc}
     */
    public function getEntityFQCN()
    {
        return 'OroCRM\Bundle\MagentoBundle\Entity\Order';
    }

    /**
     * @return array
     */
    public function getSetDataProvider()
    {
        $customer = new Customer();
        $store    = new Store();
        $cart     = new Cart();
        $items    = new ArrayCollection();
        $items->add(new OrderItem());
        $createdAt    = new \DateTime('now');
        $updatedAt    = new \DateTime('now');
        $channel      = new Channel();
        $owner        = $this->getMock('Oro\Bundle\UserBundle\Entity\User');
        $organization = $this->getMock('Oro\Bundle\OrganizationBundle\Entity\Organization');

        return [
            'incrementId'         => ['incrementId', 1, 1],
            'customer'            => ['customer', $customer, $customer],
            'store'               => ['store', $store, $store],
            'isVirtual'           => ['isVirtual', true, true],
            'isGuest'             => ['isGuest', true, true],
            'giftMessage'         => ['giftMessage', 'some message', 'some message'],
            'remoteIp'            => ['remoteIp', 'remoteIp', 'remoteIp'],
            'storeName'           => ['storeName', 'store name', 'store name'],
            'totalPaidAmount'     => ['totalPaidAmount', 100.14, 100.14],
            'totalInvoicedAmount' => ['totalInvoicedAmount', 90.55, 90.55],
            'totalRefundedAmount' => ['totalRefundedAmount', 87.82, 87.82],
            'totalCanceledAmount' => ['totalCanceledAmount', 94.47, 94.47],
            'cart'                => ['cart', $cart, $cart],
            'items'               => ['items', $items, $items],
            'notes'               => ['notes', 'notes', 'notes'],
            'feedback'            => ['feedback', 'positive', 'positive'],
            'customerEmail'       => ['customerEmail', 'email@email.com', 'email@email.com'],
            'currency'            => ['currency', 'uah', 'uah'],
            'paymentMethod'       => ['paymentMethod', 'payment method', 'payment method'],
            'paymentDetails'      => ['paymentDetails', 'payment details', 'payment details'],
            'subtotalAmount'      => ['subtotalAmount', 0.12, 0.12],
            'shippingAmount'      => ['shippingAmount', 0.12, 0.12],
            'shippingMethod'      => ['shippingMethod', 'shipping method', 'shipping method'],
            'taxAmount'           => ['taxAmount', 10.92, 10.92],
            'discountAmount'      => ['discountAmount', 90.92, 90.92],
            'discountPercent'     => ['discountPercent', 2, 2],
            'totalAmount'         => ['totalAmount', 5.22, 5.22],
            'status'              => ['status', 'status', 'status'],
            'createdAt'           => ['createdAt', $createdAt, $createdAt],
            'updatedAt'           => ['updatedAt', $updatedAt, $updatedAt],
            'channel'             => ['channel', $channel, $channel],
            'owner'               => ['owner', $owner, $owner],
            'organization'        => ['organization', $organization, $organization],
            'couponCode'          => ['couponCode', 'TEST COUPON CODE', 'TEST COUPON CODE'],
        ];
    }

    /**
     * @dataProvider isCanceledDataProvider
     * @param $status
     * @param $result
     */
    public function testIsCanceled($status, $result)
    {
        $this->entity->setStatus($status);
        if ($result) {
            $this->assertTrue($this->entity->isCanceled());
        } else {
            $this->assertFalse($this->entity->isCanceled());
        }
    }

    /**
     * @dataProvider isCompletedDataProvider
     * @param $status
     * @param $result
     */
    public function testIsCompleted($status, $result)
    {
        $this->entity->setStatus($status);
        if ($result) {
            $this->assertTrue($this->entity->isCompleted());
        } else {
            $this->assertFalse($this->entity->isCompleted());
        }

    }

    /**
     * @return array
     */
    public function isCanceledDataProvider()
    {
        return [
            'Order is canceled'              => ['canceled', true],
            'Order is canceled(ignore case)' => ['Canceled', true],
            'Order is not canceled'          => ['completed', false],
        ];
    }

    /**
     * @return array
     */
    public function isCompletedDataProvider()
    {
        return [
            'Order is completed'              => ['completed', true],
            'Order is completed(ignore case)' => ['Completed', true],
            'Order is not completed'          => ['canceled', false],
        ];
    }
}
