<?php

namespace OroCRM\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\UserBundle\Entity\User;

use OroCRM\Bundle\AnalyticsBundle\Entity\RFMMetricCategory;
use OroCRM\Bundle\ChannelBundle\Builder\BuilderFactory;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\MagentoBundle\Entity\Cart;
use OroCRM\Bundle\MagentoBundle\Entity\CartItem;
use OroCRM\Bundle\MagentoBundle\Entity\CartStatus;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Entity\CustomerGroup;
use OroCRM\Bundle\MagentoBundle\Entity\MagentoSoapTransport;
use OroCRM\Bundle\MagentoBundle\Entity\Order;
use OroCRM\Bundle\MagentoBundle\Entity\OrderAddress;
use OroCRM\Bundle\MagentoBundle\Entity\OrderItem;
use OroCRM\Bundle\MagentoBundle\Entity\Store;
use OroCRM\Bundle\MagentoBundle\Entity\Website;
use OroCRM\Bundle\MagentoBundle\Provider\Transport\SoapTransport;

class LoadMagentoData extends AbstractFixture implements ContainerAwareInterface, DependentFixtureInterface
{
    use ContainerAwareTrait;

    const TAX              = 0.0838;
    const INTEGRATION_NAME = 'Demo Web store';

    /** @var array */
    protected $users;

    /** @var  Channel */
    protected $dataChannel;

    /** @var Organization */
    protected $organization;

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            'OroCRM\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadContactData'
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $om)
    {
        $this->organization = $this->getReference('default_organization');
        $this->users = $om->getRepository('OroUserBundle:User')->findAll();

        $website = new Website();
        $website
            ->setCode('admin')
            ->setName('Admin');
        $om->persist($website);

        $store = new Store();
        $store
            ->setCode('admin')
            ->setName('Admin')
            ->setWebsite($website);
        $om->persist($store);

        $transport = new MagentoSoapTransport();
        $transport->setApiUser('api_user');
        $transport->setApiKey('api_key');
        $transport->setExtensionVersion(SoapTransport::REQUIRED_EXTENSION_VERSION);
        $transport->setIsExtensionInstalled(true);
        $transport->setMagentoVersion('1.9.1.0');
        $transport->setWsdlUrl('http://magento.domain');
        $om->persist($transport);

        $integration = new Integration();
        $integration->setType('magento');
        $integration->setConnectors(['customer', 'cart', 'order', 'newsletter_subscriber']);
        $integration->setName(self::INTEGRATION_NAME);
        $integration->setTransport($transport);
        $integration->setOrganization($this->organization);
        $om->persist($integration);

        /** @var $factory BuilderFactory */
        $factory = $this->container->get('orocrm_channel.builder.factory');
        $builder = $factory->createBuilderForIntegration($integration);
        $builder->setOwner($integration->getOrganization());
        $builder->setDataSource($integration);
        $builder->setStatus($integration->isEnabled() ? Channel::STATUS_ACTIVE : Channel::STATUS_INACTIVE);
        $this->dataChannel = $builder->getChannel();

        $om->persist($this->dataChannel);

        $group = new CustomerGroup();
        $group->setName('General');
        $group->setOriginId(15000);
        $group->setChannel($integration);
        $om->persist($group);

        $om->flush();

        $this->persistDemoCustomers($om, $website, $store, $group, $integration);
        $om->flush();

        $this->persistDemoCarts($om, $store, $integration);
        $om->flush();

        $this->persistDemoOrders($om, $store, $integration);
        $om->flush();

        $this->persistDemoRFM($om);
        $om->flush();
    }

    /**
     * @param ObjectManager $om
     */
    protected function persistDemoRFM(ObjectManager $om)
    {
        $rfmData = [
            'recency' => [
                ['min' => null, 'max' => 7],
                ['min' => 7, 'max' => 30],
                ['min' => 30, 'max' => 90],
                ['min' => 90, 'max' => 365],
                ['min' => 365, 'max' => null],
            ],
            'frequency' => [
                ['min' => 52, 'max' => null],
                ['min' => 12, 'max' => 52],
                ['min' => 4, 'max' => 12],
                ['min' => 2, 'max' => 4],
                ['min' => null, 'max' => 2],
            ],
            'monetary' => [
                ['min' => 10000, 'max' => null],
                ['min' => 1000, 'max' => 10000],
                ['min' => 100, 'max' => 1000],
                ['min' => 10, 'max' => 100],
                ['min' => null, 'max' => 10],
            ]
        ];

        foreach ($rfmData as $type => $values) {
            foreach ($values as $idx => $limits) {
                $category = new RFMMetricCategory();
                $category->setCategoryIndex($idx + 1)
                    ->setChannel($this->dataChannel)
                    ->setCategoryType($type)
                    ->setMinValue($limits['min'])
                    ->setMaxValue($limits['max'])
                    ->setOwner($this->organization);

                $om->persist($category);
            }
        }

        $data = $this->dataChannel->getData();
        $data['rfm_enabled'] = true;
        $this->dataChannel->setData($data);
        $om->persist($this->dataChannel);
    }

    /**
     * @param ObjectManager $om
     * @param Store         $store
     * @param Integration   $integration
     */
    protected function persistDemoCarts(ObjectManager $om, Store $store, Integration $integration)
    {
        /** @var Customer[] $customers */
        $customers = $om->getRepository('OroCRMMagentoBundle:Customer')->findAll();
        /** @var CartStatus $status */
        $status = $om->getRepository('OroCRMMagentoBundle:CartStatus')->findOneBy(['name' => 'open']);

        for ($i = 0; $i < 10; ++$i) {
            $customerRandom = rand(0, count($customers)-1);
            $cart = $this->generateShoppingCart($om, $store, $integration, $customers[$customerRandom], $status, $i);
            $this->generateShoppingCartItem($om, $cart);
        }
    }

    /**
     * @param ObjectManager $om
     * @param Store         $store
     * @param Integration   $integration
     */
    protected function persistDemoOrders(ObjectManager $om, Store $store, Integration $integration)
    {
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
                $integration,
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
     * @param Integration   $integration
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
        Integration $integration,
        Customer $customer,
        $status,
        Cart $cart,
        $paymentMethod,
        $paymentMethodDetails,
        $origin
    ) {
        $order = new Order();
        $order->setOrganization($this->organization);
        $order->setChannel($integration);
        $order->setCustomer($customer);
        $order->setOwner($customer->getOwner());
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
        $order->setDataChannel($this->dataChannel);
        if ($status == 'Completed') {
            $order->setTotalPaidAmount($cart->getGrandTotal());
        }
        $order->setSubtotalAmount($cart->getSubTotal());
        $order->setShippingAmount(rand(5, 10));
        $order->setPaymentMethod($paymentMethod);
        $order->setPaymentDetails($paymentMethodDetails);
        $order->setShippingMethod('flatrate_flatrate');
        $address = $this->getOrderAddress($om);
        $order->addAddress($address);
        $address->setOwner($order);
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
        $orderItems = [];
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
            $orderItem->setOwner($order->getOrganization());
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
     * @param Integration   $integration
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
        Integration $integration,
        Customer $customer,
        CartStatus $status,
        $origin,
        $currency = 'USD',
        $rate = 1
    ) {
        $cart = new Cart();
        $cart->setOrganization($this->organization);
        $cart->setChannel($integration);
        $cart->setCustomer($customer);
        $cart->setOwner($customer->getOwner());
        $cart->setStatus($status);
        $cart->setStore($store);
        $cart->setBaseCurrencyCode($currency);
        $cart->setStoreCurrencyCode($currency);
        $cart->setQuoteCurrencyCode($currency);
        $cart->setStoreToBaseRate($rate);
        $cart->setStoreToQuoteRate($rate);
        $cart->setIsGuest(0);
        $cart->setEmail($customer->getEmail());

        $datetime = new \DateTime('now');
        $datetime->modify(sprintf('-%s day', rand(1, 5)));

        $cart->setCreatedAt($datetime);
        $cart->setUpdatedAt($datetime);
        $cart->setOriginId($origin);
        $cart->setDataChannel($this->dataChannel);
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

        $products = ['Computer', 'Gaming Computer', 'Universal Camera Case', 'SLR Camera Tripod',
            'Two Year Extended Warranty - Parts and Labor', 'Couch', 'Chair', 'Magento Red Furniture Set'];

        $cartItems = [];
        $cartItemsCount = rand(0, 2);
        $total = 0.0;
        $totalTaxAmount = 0.0;
        $shipping = rand(0, 1);
        for ($i = 0; $i <= $cartItemsCount; $i++) {
            $product = $products[rand(0, count($products)-1)];
            $origin = $i+1;
            $price = rand(10, 200);
            $price = $price + rand(0, 99)/100.0;
            $taxAmount = $price * self::TAX;
            $totalTaxAmount = $totalTaxAmount + $taxAmount;
            $total = $total + $price + $taxAmount;
            $cartItem = new CartItem();
            $cartItem->setProductId(rand(1, 100));
            $cartItem->setFreeShipping((string)$shipping);
            $cartItem->setIsVirtual(0);
            $cartItem->setRowTotal($price + $taxAmount);
            $cartItem->setPriceInclTax($price + $taxAmount);
            $cartItem->setTaxAmount($taxAmount);
            $cartItem->setSku('sku-' . strtolower(str_replace(" ", "_", $product)));
            $cartItem->setProductType('simple');
            $cartItem->setName($product);
            $cartItem->setQty(1);
            $cartItem->setPrice($price);
            $cartItem->setDiscountAmount(0);
            $cartItem->setTaxPercent(self::TAX);
            $cartItem->setCreatedAt(new \DateTime('now'));
            $cartItem->setUpdatedAt(new \DateTime('now'));
            $cartItem->setOriginId($origin);
            $cartItem->setCart($cart);
            $cartItem->setOwner($cart->getOrganization());
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

    protected function getOrderAddress(ObjectManager $om)
    {
        $address = new OrderAddress;
        $address->setCity('City');
        $address->setStreet('First street');
        $address->setPostalCode(123456);
        $address->setFirstName('John');
        $address->setLastName('Doe');
        /** @var Country $country */
        $country = $om->getRepository('OroAddressBundle:Country')->findOneBy(['iso2Code' => 'US']);
        $address->setCountry($country);
        /** @var Region $region */
        $region = $om->getRepository('OroAddressBundle:Region')->findOneBy(['combinedCode' => 'US-AK']);
        $address->setRegion($region);
        $om->persist($address);

        return $address;
    }

    /**
     * @param ObjectManager $om
     * @param Website       $website
     * @param Store         $store
     * @param CustomerGroup $group
     * @param Integration   $integration
     */
    protected function persistDemoCustomers(
        ObjectManager $om,
        Website $website,
        Store $store,
        CustomerGroup $group,
        Integration $integration
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
            $customer->setWebsite($website)
                ->setChannel($integration)
                ->setStore($store)
                ->setFirstName($contact->getFirstName())
                ->setLastName($contact->getLastName())
                ->setEmail($contact->getPrimaryEmail())
                ->setBirthday($birthday)
                ->setVat(mt_rand(10000000, 99999999))
                ->setGroup($group)
                ->setCreatedAt(new \DateTime('now'))
                ->setUpdatedAt(new \DateTime('now'))
                ->setOriginId($i + 1)
                ->setAccount($accounts[$buffer[$i]])
                ->setContact($contact)
                ->setOrganization($this->organization)
                ->setOwner($this->getRandomOwner());
            $customer->setDataChannel($this->dataChannel);

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
        // Convert to timestamps
        $min = strtotime('1950-01-01');
        $max = strtotime('2000-01-01');

        // Generate random number using above bounds
        $val = rand($min, $max);

        // Convert back to desired date format
        return new \DateTime(date('Y-m-d', $val), new \DateTimeZone('UTC'));
    }

    /**
     * @return User
     */
    protected function getRandomOwner()
    {
        $randomUser = count($this->users)-1;
        $user = $this->users[rand(0, $randomUser)];

        return $user;
    }
}
