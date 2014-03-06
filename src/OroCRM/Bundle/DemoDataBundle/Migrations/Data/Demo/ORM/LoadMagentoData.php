<?php

namespace OroCRM\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

use Oro\Bundle\IntegrationBundle\Entity\Channel;
use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\MagentoBundle\Entity\Cart;
use OroCRM\Bundle\MagentoBundle\Entity\CartItem;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Entity\CustomerGroup;
use OroCRM\Bundle\MagentoBundle\Entity\MagentoSoapTransport;
use OroCRM\Bundle\MagentoBundle\Entity\OrderItem;
use OroCRM\Bundle\MagentoBundle\Entity\Store;
use OroCRM\Bundle\MagentoBundle\Entity\Website;
use OroCRM\Bundle\MagentoBundle\Entity\CartStatus;
use OroCRM\Bundle\MagentoBundle\Entity\Order;

class LoadMagentoData extends AbstractFixture implements DependentFixtureInterface
{
    const VAT = 0.0838;
    const CHANNEL_NAME = 'Demo Web store';

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return ['OroCRM\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadContactData'];
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $om)
    {
        $website = new Website();
        $website->setCode('admin')
            ->setName('Admin');

        $om->persist($website);

        $store = new Store();
        $store->setCode('admin')
            ->setName('Admin')
            ->setWebsite($website);

        $om->persist($website);

        $group = new CustomerGroup();
        $group->setName('General');

        $om->persist($group);

        $transport = new MagentoSoapTransport();
        $transport->setApiUser('api_user');
        $transport->setApiKey('api_key');
        $transport->setWsdlUrl('http://magento.domain');
        $om->persist($transport);

        $channel = new Channel();
        $channel->setType('magento');
        $channel->setConnectors(['customer', 'cart', 'order']);
        $channel->setName(self::CHANNEL_NAME);

        $channel->setTransport($transport);

        $om->persist($channel);

        $this->persistDemoCustomers($om, $website, $store, $group, $channel);
        $om->flush();
        $this->persistDemoCarts($om, $store, $channel);
        $om->flush();
        $this->persistDemoOrders($om, $store, $channel);
        $om->flush();
    }

    /**
     * @param ObjectManager                                     $om
     * @param Store                                             $store
     * @param Channel $channel
     */
    protected function persistDemoCarts(
        ObjectManager $om,
        Store $store,
        Channel $channel
    ) {
        /** @var Customer[] $customers */
        $customers = $om->getRepository('OroCRMMagentoBundle:Customer')->findAll();
        /** @var CartStatus $status */
        $status = $om->getRepository('OroCRMMagentoBundle:CartStatus')->findOneBy(array('name' => 'open'));

        for ($i = 0; $i < 10; ++$i) {
            $customerRandom = rand(0, count($customers)-1);
            $cart = $this->generateShoppingCart($om, $store, $channel, $customers[$customerRandom], $status, $i);
            $this->generateShoppingCartItem($om, $cart);
        }
    }

    /**
     * @param ObjectManager                                     $om
     * @param Store                                             $store
     * @param Channel $channel
     */
    protected function persistDemoOrders(
        ObjectManager $om,
        Store $store,
        Channel $channel
    ) {
        /** @var Cart[] $carts */
        $carts = $om->getRepository('OroCRMMagentoBundle:Cart')->findAll();

        $paymentMethod = ['Ccsave', 'Checkmo'];
        $paymentMethodDetails = ['Card[MC]', 'Card[AE]', 'N/A'];
        $status = ['Pending', 'Processing', 'Completed', 'Canceled'];
        $i = 0;
        foreach ($carts as $cart) {
            /** @var Cart $cart */
            $order = $this->generateOrder(
                $om,
                $store,
                $channel,
                $cart->getCustomer(),
                $status[rand(0, count($status)-1)],
                $cart,
                $paymentMethod[rand(0, count($paymentMethod)-1)],
                $paymentMethodDetails[rand(0, count($paymentMethodDetails)-1)],
                $i++
            );
            $this->generateOrderItem($om, $order, $cart);
        }
    }

    /**
     * @param ObjectManager $om
     * @param Store         $store
     * @param Channel       $channel
     * @param Customer      $customer
     * @param string        $status
     * @param Cart          $cart
     * @param string        $paymentMethod
     * @param string        $paymentMethodDetails
     * @param mixed         $origin
     *
     * @return Order
     */
    protected function generateOrder(
        ObjectManager $om,
        Store $store,
        Channel $channel,
        Customer $customer,
        $status,
        Cart $cart,
        $paymentMethod,
        $paymentMethodDetails,
        $origin
    ) {
        $order = new Order();
        $order->setChannel($channel);
        $order->setCustomer($customer);
        $order->setStatus($status);
        $order->setStore($store);
        $order->setStoreName($store->getName());
        $order->setIsGuest(0);
        $order->setIncrementId((string)$origin);
        $order->setCreatedAt(new \DateTime('now'));
        $order->setUpdatedAt(new \DateTime('now'));
        $order->setCart($cart);
        $order->setCurrency($cart->getBaseCurrencyCode());
        $order->setTotalAmount($cart->getGrandTotal());
        $order->setTotalInvoicedAmount($cart->getGrandTotal());
        if ($status == 'Completed') {
            $order->setTotalPaidAmount($cart->getGrandTotal());
        }
        $order->setSubtotalAmount($cart->getSubTotal());
        $order->setShippingAmount(rand(5, 10));
        $order->setPaymentMethod($paymentMethod);
        $order->setPaymentDetails($paymentMethodDetails);
        $order->setShippingMethod('flatrate_flatrate');
        $om->persist($order);
        return $order;
    }


    /**
     * @param ObjectManager $om
     * @param Order         $order
     * @param Cart          $cart
     *
     * @return OrderItem[]
     */
    protected function generateOrderItem(ObjectManager $om, Order $order, Cart $cart)
    {
        $cartItems = $cart->getCartItems();
        $orderItems = array();
        foreach ($cartItems as $cartItem) {
            $orderItem = new OrderItem();
            $orderItem->setOriginId($cartItem->getOriginId());
            $orderItem->setOrder($order);
            $orderItem->setTaxAmount($cartItem->getTaxAmount());
            $orderItem->setTaxPercent($cartItem->getTaxPercent());
            $orderItem->setRowTotal($cartItem->getRowTotal());
            $orderItem->setProductType($cartItem->getProductType());
            $orderItem->setIsVirtual((bool)$cartItem->getIsVirtual());
            $orderItem->setQty($cartItem->getQty());
            $orderItem->setSku($cartItem->getSku());
            $orderItem->setPrice($cartItem->getPrice());
            $orderItem->setOriginalPrice($cartItem->getPrice());
            $orderItem->setName($cartItem->getName());
            $orderItems[] = $orderItem;

            $om->persist($orderItem);
        }

        $order->setItems($orderItems);
        $om->persist($order);

        return $orderItems;
    }

    /**
     * @param ObjectManager $om
     * @param Store         $store
     * @param Channel       $channel
     * @param Customer      $customer
     * @param CartStatus    $status
     * @param int           $origin
     * @param string        $currency
     * @param int           $rate
     *
     * @return Cart
     */
    protected function generateShoppingCart(
        ObjectManager $om,
        Store $store,
        Channel $channel,
        Customer $customer,
        CartStatus $status,
        $origin,
        $currency = 'USD',
        $rate = 1
    ) {
        $cart = new Cart();
        $cart->setChannel($channel);
        $cart->setCustomer($customer);
        $cart->setStatus($status);
        $cart->setStore($store);
        $cart->setBaseCurrencyCode($currency);
        $cart->setStoreCurrencyCode($currency);
        $cart->setQuoteCurrencyCode($currency);
        $cart->setStoreToBaseRate($rate);
        $cart->setStoreToQuoteRate($rate);
        $cart->setIsGuest(0);
        $cart->setEmail($customer->getEmail());
        $cart->setCreatedAt(new \DateTime('now'));
        $cart->setUpdatedAt(new \DateTime('now'));
        $cart->setOriginId($origin);
        $om->persist($cart);

        return $cart;
    }

    /**
     * @param ObjectManager $om
     * @param Cart          $cart
     *
     * @return CartItem[]
     */
    protected function generateShoppingCartItem(ObjectManager $om, Cart $cart)
    {

        $products = array('Computer', 'Gaming Computer', 'Universal Camera Case', 'SLR Camera Tripod',
            'Two Year Extended Warranty - Parts and Labor', 'Couch', 'Chair', 'Magento Red Furniture Set');

        $cartItems = array();
        $cartItemsCount = rand(0, 2);
        $total = 0.0;
        $totalTaxAmount = 0.0;
        $shipping = rand(0, 1);
        for ($i = 0; $i <= $cartItemsCount; $i++) {
            $product = $products[rand(0, count($products)-1)];
            $origin = $i+1;
            $price = rand(10, 200);
            $price = $price + rand(0, 99)/100.0;
            $taxAmount = $price * self::VAT;
            $totalTaxAmount = $totalTaxAmount + $taxAmount;
            $total = $total + $price + $taxAmount;
            $cartItem = new CartItem();
            $cartItem->setProductId(rand(1, 100));
            $cartItem->setFreeShipping((string)$shipping);
            $cartItem->setIsVirtual(0);
            $cartItem->setRowTotal($price + $taxAmount);
            $cartItem->setPriceInclTax($price + $taxAmount);
            $cartItem->setTaxAmount($taxAmount);
            $cartItem->setSku('sku-' . $product);
            $cartItem->setProductType('simple');
            $cartItem->setName($product);
            $cartItem->setQty(1);
            $cartItem->setPrice($price);
            $cartItem->setDiscountAmount(0);
            $cartItem->setTaxPercent(self::VAT);
            $cartItem->setCreatedAt(new \DateTime('now'));
            $cartItem->setUpdatedAt(new \DateTime('now'));
            $cartItem->setOriginId($origin);
            $cartItem->setCart($cart);
            $cart->getCartItems()->add($cartItem);
            $cart->setItemsQty($i+1);
            $cart->setItemsCount($i+1);
            $om->persist($cartItem);
            $cartItems[] = $cartItem;
        }
        $cart->setSubTotal($total);
        $shippingAmount = 0.0;
        if ((bool)$shipping) {
            $shippingAmount = rand(3, 10);
        }
        $cart->setGrandTotal($total + $shippingAmount);
        $cart->setTaxAmount($totalTaxAmount);
        $om->persist($cart);
        return $cartItems;
    }

    /**
     * @param ObjectManager                                     $om
     * @param Website                                           $website
     * @param Store                                             $store
     * @param CustomerGroup $group
     * @param Channel $channel
     */
    protected function persistDemoCustomers(
        ObjectManager $om,
        Website $website,
        Store $store,
        CustomerGroup $group,
        Channel $channel
    ) {
        $accounts = $om->getRepository('OroCRMAccountBundle:Account')->findAll();
        $contacts = $om->getRepository('OroCRMContactBundle:Contact')->findAll();

        $buffer = range(0, 48);
        shuffle($buffer);
        for ($i = 0; $i < 49; ++$i) {
            $birthday  = $this->generateBirthday();

            /** @var Contact $contact */
            $contact = $contacts[$buffer[$i]];
            $customer = new Customer();
            if (is_null($accounts[$buffer[$i]])) {
                var_dump($buffer[$i]);
            }
            $customer->setWebsite($website)
                ->setChannel($channel)
                ->setStore($store)
                ->setFirstName($contact->getFirstName())
                ->setLastName($contact->getLastName())
                ->setEmail($contact->getPrimaryEmail())
                ->setBirthday($birthday)
                ->setVat(self::VAT * 100.0)
                ->setGroup($group)
                ->setCreatedAt(new \DateTime('now'))
                ->setUpdatedAt(new \DateTime('now'))
                ->setOriginId($i + 1)
                ->setAccount($accounts[$buffer[$i]])
                ->setContact($contact);

            $om->persist($customer);
        }
    }

    /**
     * Generates a date of birth
     *
     * @return \DateTime
     */
    private function generateBirthday()
    {
        // Convert to timetamps
        $min = strtotime('1950-01-01');
        $max = strtotime('2000-01-01');

        // Generate random number using above bounds
        $val = rand($min, $max);

        // Convert back to desired date format
        return new \DateTime(date('Y-m-d', $val), new \DateTimeZone('UTC'));
    }
}
