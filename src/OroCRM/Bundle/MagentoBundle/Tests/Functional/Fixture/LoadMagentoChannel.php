<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Functional\Fixture;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\AddressBundle\Entity\Address;
use Oro\Bundle\DataGridBundle\Common\DataObject;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Model\Gender;

use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\ChannelBundle\Builder\BuilderFactory;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\MagentoBundle\Entity\Address as MagentoAddress;
use OroCRM\Bundle\MagentoBundle\Entity\Cart;
use OroCRM\Bundle\MagentoBundle\Entity\CartAddress;
use OroCRM\Bundle\MagentoBundle\Entity\CartItem;
use OroCRM\Bundle\MagentoBundle\Entity\CartStatus;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Entity\CustomerGroup;
use OroCRM\Bundle\MagentoBundle\Entity\MagentoSoapTransport;
use OroCRM\Bundle\MagentoBundle\Entity\Order;
use OroCRM\Bundle\MagentoBundle\Entity\OrderItem;
use OroCRM\Bundle\MagentoBundle\Entity\Store;
use OroCRM\Bundle\MagentoBundle\Entity\Website;
use OroCRM\Bundle\MagentoBundle\Provider\Transport\SoapTransport;

class LoadMagentoChannel extends AbstractFixture implements ContainerAwareInterface
{
    const CHANNEL_NAME = 'Magento channel';
    const CHANNEL_TYPE = 'magento';

    /** @var ObjectManager */
    protected $em;

    /** @var integration */
    protected $integration;

    /** @var MagentoSoapTransport */
    protected $transport;

    /** @var array */
    protected $countries;

    /** @var array */
    protected $regions;

    /** @var Website */
    protected $website;

    /** @var Store */
    protected $store;

    /** @var CustomerGroup */
    protected $customerGroup;

    /** @var Channel */
    protected $channel;

    /** @var BuilderFactory */
    protected $factory;

    /**
     * @var Organization
     */
    protected $organization;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->factory = $container->get('orocrm_channel.builder.factory');
    }

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
            ->createStore();

        $magentoAddress = $this->createMagentoAddress($this->regions['US-AZ'], $this->countries['US']);
        $account        = $this->createAccount();
        $this->setReference('account', $account);

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

        $this->setReference('customer', $customer);
        $this->setReference('integration', $this->integration);
        $this->setReference('cart', $cart);
        $this->setReference('order', $order);

        $baseOrderItem = $this->createBaseOrderItem($order);
        $order->setItems([$baseOrderItem]);
        $this->em->persist($order);

        $this->em->flush();
    }

    /**
     * @param                 $billing
     * @param                 $shipping
     * @param Customer        $customer
     * @param ArrayCollection $item
     * @param CartStatus      $status
     *
     * @return Cart
     */
    protected function createCart($billing, $shipping, Customer $customer, ArrayCollection $item, $status)
    {
        $cart = new Cart();
        $cart->setOriginId(100);
        $cart->setChannel($this->integration);
        $cart->setDataChannel($this->channel);
        $cart->setBillingAddress($billing);
        $cart->setShippingAddress($shipping);
        $cart->setCustomer($customer);
        $cart->setEmail('email@email.com');
        $cart->setCreatedAt(new \DateTime('now'));
        $cart->setUpdatedAt(new \DateTime('now'));
        $cart->setCartItems($item);
        $cart->setStatus($status);
        $cart->setItemsQty(0);
        $cart->setItemsCount(1);
        $cart->setBaseCurrencyCode('code');
        $cart->setStoreCurrencyCode('code');
        $cart->setQuoteCurrencyCode('usd');
        $cart->setStoreToBaseRate(12);
        $cart->setStoreToQuoteRate(12);
        $cart->setGrandTotal(2.54);
        $cart->setIsGuest(0);
        $cart->setStore($this->store);
        $cart->setOwner($this->getUser());
        $cart->setOrganization($this->organization);

        $this->em->persist($cart);

        return $cart;
    }

    /**
     * @param $table
     * @param $method
     *
     * @return array
     */
    protected function loadStructure($table, $method)
    {
        $result   = [];
        $response = $this->em->getRepository($table)->findAll();
        foreach ($response as $row) {
            $result[call_user_func([$row, $method])] = $row;
        }

        return $result;
    }

    /**
     * @return $this
     */
    protected function createIntegration()
    {
        $integration = new Integration();
        $integration->setName('Demo Web store');
        $integration->setType('magento');
        $integration->setConnectors(['customer', 'order', 'cart']);
        $integration->setTransport($this->transport);
        $integration->setOrganization($this->organization);

        $synchronizationSettings = DataObject::create(['isTwoWaySyncEnabled' => true]);
        $integration->setSynchronizationSettings($synchronizationSettings);

        $this->em->persist($integration);
        $this->integration = $integration;

        return $this;
    }

    /**
     * @return $this
     */
    protected function createTransport()
    {
        $transport = new MagentoSoapTransport;
        $transport->setAdminUrl('http://localhost/magento/admin');
        $transport->setApiKey('key');
        $transport->setApiUser('user');
        $transport->setIsExtensionInstalled(true);
        $transport->setExtensionVersion(SoapTransport::REQUIRED_EXTENSION_VERSION);
        $transport->setMagentoVersion('1.9.1.0');
        $transport->setIsWsiMode(false);
        $transport->setWebsiteId('1');
        $transport->setWsdlUrl('http://localhost/magento/api/v2_soap?wsdl=1');
        $transport->setWebsites([['id' => 1, 'label' => 'Website ID: 1, Stores: English, French, German']]);

        $this->em->persist($transport);
        $this->transport = $transport;

        return $this;
    }

    /**
     * @param $region
     * @param $country
     * @param $originId
     *
     * @return CartAddress
     */
    protected function createCartAddress($region, $country, $originId)
    {
        $cartAddress = new CartAddress;
        $cartAddress->setRegion($region);
        $cartAddress->setCountry($country);
        $cartAddress->setCity('City');
        $cartAddress->setStreet('street');
        $cartAddress->setPostalCode(123456);
        $cartAddress->setFirstName('John');
        $cartAddress->setLastName('Doe');
        $cartAddress->setOriginId($originId);
        $cartAddress->setOrganization($this->organization);

        $this->em->persist($cartAddress);

        return $cartAddress;
    }

    /**
     * @param $region
     * @param $country
     *
     * @return MagentoAddress
     */
    protected function createMagentoAddress($region, $country)
    {
        $address = new MagentoAddress;
        $address->setRegion($region);
        $address->setCountry($country);
        $address->setCity('City');
        $address->setStreet('street');
        $address->setPostalCode(123456);
        $address->setFirstName('John');
        $address->setLastName('Doe');
        $address->setLabel('label');
        $address->setPrimary(true);
        $address->setOrganization('oro');
        $address->setOriginId(1);
        $address->setChannel($this->integration);
        $address->setOrganization($this->organization);

        $this->em->persist($address);

        return $address;
    }

    /**
     * @param $region
     * @param $country
     *
     * @return Address
     */
    protected function createAddress($region, $country)
    {
        $address = new Address;
        $address->setRegion($region);
        $address->setCountry($country);
        $address->setCity('City');
        $address->setStreet('street');
        $address->setPostalCode(123456);
        $address->setFirstName('John');
        $address->setLastName('Doe');
        $address->setOrganization($this->organization);

        $this->em->persist($address);

        return $address;
    }

    /**
     * @param                $oid
     * @param Account        $account
     * @param MagentoAddress $address
     *
     * @return Customer
     */
    protected function createCustomer($oid, Account $account, MagentoAddress $address)
    {
        $customer = new Customer();
        $customer->setChannel($this->integration);
        $customer->setDataChannel($this->channel);
        $customer->setFirstName('John');
        $customer->setLastName('Doe');
        $customer->setEmail('test@example.com');
        $customer->setOriginId($oid);
        $customer->setIsActive(true);
        $customer->setWebsite($this->website);
        $customer->setStore($this->store);
        $customer->setAccount($account);
        $customer->setGender(Gender::MALE);
        $customer->setGroup($this->customerGroup);
        // TODO: DateTimeZones should be removed in BAP-8710. Tests should be passed for:
        //  - OroCRM\Bundle\MagentoBundle\Tests\Functional\Controller\Api\Rest\CustomerControllerTest
        //  - OroCRM\Bundle\MagentoBundle\Tests\Functional\Controller\Api\Rest\MagentoCustomerControllerTest
        $customer->setCreatedAt(new \DateTime('now', new \DateTimezone('UTC')));
        $customer->setUpdatedAt(new \DateTime('now', new \DateTimezone('UTC')));
        $customer->addAddress($address);
        $customer->setOwner($this->getUser());
        $customer->setOrganization($this->organization);

        $this->em->persist($customer);

        return $customer;
    }

    /**
     * @return $this
     */
    protected function createWebSite()
    {
        $website = new Website();
        $website->setName('web site');
        $website->setOriginId(1);
        $website->setCode('web site code');
        $website->setChannel($this->integration);

        $this->setReference('website', $website);
        $this->em->persist($website);
        $this->website = $website;

        return $this;
    }

    /**
     * @return $this
     */
    protected function createStore()
    {
        $store = new Store;
        $store->setName('demo store');
        $store->setChannel($this->integration);
        $store->setCode(1);
        $store->setWebsite($this->website);
        $store->setOriginId(1);

        $this->em->persist($store);
        $this->store = $store;
        $this->setReference('store', $store);

        return $this;
    }

    /**
     * @return Account
     */
    protected function createAccount()
    {
        $account = new Account;
        $account->setName('acc');
        $account->setOwner($this->getUser());
        $account->setOrganization($this->organization);

        $this->em->persist($account);

        return $account;
    }

    /**
     * @return $this
     */
    protected function createCustomerGroup()
    {
        $customerGroup = new CustomerGroup;
        $customerGroup->setName('group');
        $customerGroup->setChannel($this->integration);
        $customerGroup->setOriginId(1);

        $this->em->persist($customerGroup);
        $this->setReference('customer_group', $customerGroup);
        $this->customerGroup = $customerGroup;

        return $this;
    }

    /**
     * @return CartItem
     */
    protected function createCartItem()
    {
        $cartItem = new CartItem();
        $cartItem->setName('item' . mt_rand(0, 99999));
        $cartItem->setDescription('something');
        $cartItem->setPrice(mt_rand(10, 99999));
        $cartItem->setProductId(1);
        $cartItem->setFreeShipping('true');
        $cartItem->setIsVirtual(1);
        $cartItem->setRowTotal(100);
        $cartItem->setTaxAmount(10);
        $cartItem->setProductType('type');
        $cartItem->setSku('sku');
        $cartItem->setQty(0);
        $cartItem->setDiscountAmount(0);
        $cartItem->setTaxPercent(0);
        $cartItem->setCreatedAt(new \DateTime('now'));
        $cartItem->setUpdatedAt(new \DateTime('now'));
        $cartItem->setOwner($this->organization);

        $this->em->persist($cartItem);

        return $cartItem;
    }

    /**
     * @return CartStatus
     */
    protected function getStatus()
    {
        $status = $this->em->getRepository('OroCRMMagentoBundle:CartStatus')->findOneBy(['name' => 'open']);

        return $status;
    }

    /**
     * @param CartItem $cartItem
     * @param Cart     $cart
     *
     * @return $this
     */
    protected function updateCartItem(CartItem $cartItem, Cart $cart)
    {
        $cartItem->setCart($cart);
        $this->em->persist($cartItem);

        return $this;
    }

    /**
     * @param Cart     $cart
     * @param Customer $customer
     *
     * @return Order
     */
    protected function createOrder(Cart $cart, Customer $customer)
    {
        $order = new Order();
        $order->setChannel($this->integration);
        $order->setDataChannel($this->channel);
        $order->setStatus('open');
        $order->setIncrementId('100000307');
        $order->setCreatedAt(new \DateTime('now'));
        $order->setUpdatedAt(new \DateTime('now'));
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
     * @param Order $order
     * @return OrderItem
     */
    protected function createBaseOrderItem(Order $order)
    {
        $orderItem = new OrderItem();
        $orderItem->setId(mt_rand(0, 9999));
        $orderItem->setName('some order item');
        $orderItem->setSku('some sku');
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

    /**
     * @return User
     */
    protected function getUser()
    {
        $user = $this->em->getRepository('OroUserBundle:User')->findOneBy(['username' => 'admin']);

        return $user;
    }

    /**
     * @return LoadMagentoChannel
     */
    protected function createChannel()
    {
        $channel = $this
            ->factory
            ->createBuilder()
            ->setName(self::CHANNEL_NAME)
            ->setChannelType(self::CHANNEL_TYPE)
            ->setStatus(Channel::STATUS_ACTIVE)
            ->setDataSource($this->integration)
            ->setOwner($this->organization)
            ->setEntities()
            ->getChannel();

        $this->em->persist($channel);
        $this->em->flush();

        $this->setReference('default_channel', $channel);

        $this->channel = $channel;

        return $this;
    }
}
