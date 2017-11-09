<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\Fixtures;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\MagentoBundle\Entity\Order;
use Oro\Bundle\MagentoBundle\Entity\OrderNote;
use Oro\Bundle\MagentoBundle\Tests\Functional\Fixture\LoadMagentoChannel;

class LoadOrderNotesData extends LoadMagentoChannel
{
    const DEFAULT_ORIGIN_ID = 100000200;
    const DEFAULT_MESSAGE   = 'Default test message';

    const DEFAULT_ORDER_REFERENCE_ALIAS = 'default_order';

    /** {@inheritdoc} */
    public function load(ObjectManager $manager)
    {
        $this->em = $manager;
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

        $account = $this->createAccount();
        $magentoAddress = $this->createMagentoAddress($this->regions['US-AZ'], $this->countries['US']);

        $customer       = $this->createCustomer(1, $account, $magentoAddress);
        $cartAddress1   = $this->createCartAddress($this->regions['US-AZ'], $this->countries['US'], 1);
        $cartAddress2   = $this->createCartAddress($this->regions['US-AZ'], $this->countries['US'], 2);
        $cartItem       = $this->createCartItem();
        $status         = $this->getStatus();
        $items          = new ArrayCollection();
        $items->add($cartItem);

        $cart = $this->createCart($cartAddress1, $cartAddress2, $customer, $items, $status);
        $this->updateCartItem($cartItem, $cart);

        $order = $this->createOrder($cart, $customer);

        $baseOrderItem = $this->createBaseOrderItem($order);
        $order->setItems([$baseOrderItem]);
        $this->em->persist($order);

        $cartAddress3 = $this->createGuestCartAddress($this->regions['US-AZ'], $this->countries['US'], null);
        $cartAddress4 = $this->createGuestCartAddress($this->regions['US-AZ'], $this->countries['US'], null);

        $cartItem = $this->createCartItem();
        $status   = $this->getStatus();
        $items    = new ArrayCollection();
        $items->add($cartItem);
        $guestCart = $this->createGuestCart($cartAddress3, $cartAddress4, $items, $status);
        $this->updateCartItem($cartItem, $guestCart);
        $guestOrder = $this->createGuestOrder($guestCart);

        $baseOrderItem = $this->createBaseOrderItem($guestOrder);
        $order->setItems([$baseOrderItem]);
        $this->em->persist($guestOrder);

        $baseOrderNote = $this->createOrderNote();
        $order->addOrderNote($baseOrderNote);
        $this->em->persist($order);

        $this->setReference(self::DEFAULT_ORDER_REFERENCE_ALIAS, $order);

        $this->em->flush();
    }

    /**
     * @param int               $originId
     * @param string            $message
     * @param \DateTime|null    $createdAt
     * @param \DateTime|null    $updatedAt
     *
     * @return OrderNote
     */
    protected function createOrderNote(
        $originId = self::DEFAULT_ORIGIN_ID,
        $message = self::DEFAULT_MESSAGE,
        \DateTime $createdAt = null,
        \DateTime $updatedAt = null
    ) {
        $orderNote = new OrderNote();

        $orderNote->setOriginId($originId);
        $orderNote->setMessage($message);

        if (null === $createdAt) {
            $createdAt = $this->getDefaultDateTime();
        }

        if (null === $updatedAt) {
            $updatedAt = $this->getDefaultDateTime();
        }

        $orderNote->setCreatedAt($createdAt);
        $orderNote->setUpdatedAt($updatedAt);

        $orderNote->setChannel($this->integration);

        $this->em->persist($orderNote);

        return $orderNote;
    }

    /**
     * @param string $datetime
     *
     * @return \DateTime
     */
    private function getDefaultDateTime($datetime = 'now')
    {
        $timezone = new \DateTimeZone('UTC');
        $date = new \DateTime($datetime, $timezone);

        return $date;
    }
}
