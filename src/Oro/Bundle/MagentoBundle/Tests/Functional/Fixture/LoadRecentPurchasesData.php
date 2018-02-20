<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\Fixture;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\MagentoBundle\Entity\Cart;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Entity\Order;
use Oro\Bundle\MagentoBundle\Entity\OrderItem;

class LoadRecentPurchasesData extends LoadMagentoChannel
{
    const ORDER_STATUS_OPEN = 'open';
    const ORDER_STATUS_CANCELED = 'canceled';

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $this->em        = $manager;
        $this->countries = $this->loadStructure('OroAddressBundle:Country', 'getIso2Code');
        $this->regions   = $this->loadStructure('OroAddressBundle:Region', 'getCombinedCode');
        $this->organization = $manager->getRepository('OroOrganizationBundle:Organization')->getFirst();

        $this
            ->createTransport()
            ->createIntegration()
            ->createChannel()
            ->createWebSite()
            ->createCustomerGroup()
            ->createGuestCustomerGroup()
            ->createStore();

        $magentoAddress = $this->createMagentoAddress($this->regions['US-AZ'], $this->countries['US']);
        $account        = $this->createAccount();
        $this->setReference('account', $account);

        $customer       = $this->createCustomer(1, $account, $magentoAddress);
        $customer2      = $this->createCustomer(2, $account, $magentoAddress);
        $cartAddress1   = $this->createCartAddress($this->regions['US-AZ'], $this->countries['US'], 1);
        $cartAddress2   = $this->createCartAddress($this->regions['US-AZ'], $this->countries['US'], 2);
        $cartItem       = $this->createCartItem();
        $status         = $this->getStatus();
        $items          = new ArrayCollection();
        $items->add($cartItem);

        $cart = $this->createCart($cartAddress1, $cartAddress2, $customer, $items, $status);
        $this->updateCartItem($cartItem, $cart);

        $dateNow = new \DateTime('now', new \DateTimeZone('UTC'));
        $date = new \DateTime('now', new \DateTimeZone('UTC'));
        $date->modify('-3 day');

        $order = $this->createOrder($cart, $customer);
        $order2 = $this->createOrder($cart, $customer, '100000505', 2, $date);
        $order3 = $this->createOrder($cart, $customer2, '100000602', 3, $date);
        $order4 = $this->createOrder($cart, $customer, '100000777', 4, $dateNow, self::ORDER_STATUS_CANCELED);

        $creditMemo = $this->createCreditMemo($order);

        $this->setReference('customer', $customer);
        $this->setReference('integration', $this->integration);
        $this->setReference('store', $this->store);
        $this->setReference('organization', $this->organization);
        $this->setReference('user', $this->getUser());
        $this->setReference('cart', $cart);
        $this->setReference('order', $order);
        $this->setReference('order2', $order2);
        $this->setReference('creditMemo', $creditMemo);

        $baseOrderItem = $this->createBaseOrderItem($order);
        $order->setItems([$baseOrderItem]);
        $this->em->persist($order);

        $baseOrderItem2 = $this->createBaseOrderItem($order2, 'some sku2');
        $order2->setItems([$baseOrderItem2]);
        $this->em->persist($order2);

        $baseOrderItem3 = $this->createBaseOrderItem($order3, 'some sku3');
        $order3->setItems([$baseOrderItem3]);
        $this->em->persist($order3);

        $baseOrderItem4 = $this->createBaseOrderItem($order4, 'some sku4');
        $order4->setItems([$baseOrderItem4]);
        $this->em->persist($order4);

        $cartAddress3 = $this->createGuestCartAddress($this->regions['US-AZ'], $this->countries['US'], null);
        $cartAddress4 = $this->createGuestCartAddress($this->regions['US-AZ'], $this->countries['US'], null);

        $cartItem = $this->createCartItem();
        $status   = $this->getStatus();
        $items    = new ArrayCollection();
        $items->add($cartItem);
        $guestCart = $this->createGuestCart($cartAddress3, $cartAddress4, $items, $status);
        $this->updateCartItem($cartItem, $guestCart);
        $guestOrder = $this->createGuestOrder($guestCart);

        $this->setReference('guestCart', $guestCart);
        $this->setReference('guestOrder', $guestOrder);

        $baseOrderItem = $this->createBaseOrderItem($guestOrder);
        $order->setItems([$baseOrderItem]);
        $this->em->persist($guestOrder);

        $this->em->flush();
    }

    /**
     * @param Cart              $cart
     * @param Customer          $customer
     * @param string            $incrementId
     * @param \DateTime|null    $createdAt
     * @param int               $originId
     * @param string            $status
     *
     * @return Order
     */
    protected function createOrder(
        Cart $cart,
        Customer $customer,
        $incrementId = '100000307',
        $originId = 1,
        \DateTime $createdAt = null,
        $status = self::ORDER_STATUS_OPEN
    ) {
        $createdAt = $createdAt ? $createdAt : new \DateTime('now', new \DateTimeZone('UTC'));

        $order = new Order();
        $order->setChannel($this->integration);
        $order->setDataChannel($this->channel);
        $order->setStatus($status);
        $order->setOriginId($originId);
        $order->setIncrementId($incrementId);
        $order->setCreatedAt($createdAt);
        $order->setUpdatedAt(new \DateTime('now', new \DateTimeZone('UTC')));
        $order->setCart($cart);
        $order->setStore($this->store);
        $order->setCustomer($customer);
        $order->setCustomerEmail('customer@email.com');
        $order->setDiscountAmount(4.40);
        $order->setTaxAmount(12.47);
        $order->setShippingAmount(5);
        $order->setTotalPaidAmount(17.85);
        $order->setTotalInvoicedAmount(11);
        $order->setTotalRefundedAmount(4);
        $order->setTotalCanceledAmount(0);
        $order->setShippingMethod('some unique shipping method');
        $order->setRemoteIp('127.0.0.1');
        $order->setGiftMessage('some very unique gift message');
        $order->setOwner($this->getUser());
        $order->setOrganization($this->organization);

        $this->em->persist($order);

        return $order;
    }

    /**
     * @param Cart      $cart
     * @param string    $incrementId
     *
     * @return Order
     */
    protected function createGuestOrder(Cart $cart, $incrementId = '100000308')
    {
        $order = new Order();
        $order->setChannel($this->integration);
        $order->setDataChannel($this->channel);
        $order->setStatus('open');
        $order->setIncrementId($incrementId);
        $order->setCreatedAt(new \DateTime('now'));
        $order->setUpdatedAt(new \DateTime('now'));
        $order->setCart($cart);
        $order->setStore($this->store);
        $order->setCustomer(null);
        $order->setIsGuest(1);
        $order->setCustomerEmail('guest@email.com');
        $order->setDiscountAmount(4.40);
        $order->setTaxAmount(12.47);
        $order->setShippingAmount(5);
        $order->setTotalPaidAmount(17.85);
        $order->setTotalInvoicedAmount(11);
        $order->setTotalRefundedAmount(4);
        $order->setTotalCanceledAmount(0);
        $order->setShippingMethod('some unique shipping method');
        $order->setRemoteIp('127.0.0.1');
        $order->setGiftMessage('some very unique gift message');
        $order->setOwner($this->getUser());
        $order->setOrganization($this->organization);

        $this->em->persist($order);

        return $order;
    }

    /**
     * @param Order     $order
     * @param string    $sku
     *
     * @return OrderItem
     */
    protected function createBaseOrderItem(Order $order, $sku = 'some sku')
    {
        $orderItem = new OrderItem();
        $orderItem->setId(mt_rand(0, 9999));
        $orderItem->setName('some order item');
        $orderItem->setSku($sku);
        $orderItem->setQty(1);
        $orderItem->setOrder($order);
        $orderItem->setCost(51.00);
        $orderItem->setPrice(75.00);
        $orderItem->setWeight(6.12);
        $orderItem->setTaxPercent(2);
        $orderItem->setTaxAmount(1.5);
        $orderItem->setDiscountPercent(4);
        $orderItem->setDiscountAmount(0);
        $orderItem->setRowTotal(234);
        $orderItem->setOwner($this->organization);

        $this->em->persist($orderItem);

        return $orderItem;
    }
}
