<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\Fixture\CopyCustomerAddressToContact;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\AddressBundle\Entity\Address;
use Oro\Bundle\ChannelBundle\Builder\BuilderFactory;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\MagentoBundle\Entity\Address as MagentoAddress;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Entity\CustomerGroup;
use Oro\Bundle\MagentoBundle\Entity\MagentoSoapTransport;
use Oro\Bundle\MagentoBundle\Entity\MagentoTransport;
use Oro\Bundle\MagentoBundle\Entity\Order;
use Oro\Bundle\MagentoBundle\Entity\OrderItem;
use Oro\Bundle\MagentoBundle\Entity\Store;
use Oro\Bundle\MagentoBundle\Entity\Website;
use Oro\Bundle\MagentoBundle\Provider\Transport\SoapTransport;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SalesBundle\Entity\Customer as CustomerAssociation;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Model\Gender;
use Oro\Component\Config\Common\ConfigObject;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadMagentoChannel extends AbstractFixture implements ContainerAwareInterface
{
    const CHANNEL_NAME = 'Magento channel';
    const CHANNEL_TYPE = 'magento';

    /** @var ObjectManager */
    protected $em;

    /** @var integration */
    protected $integration;

    /** @var MagentoTransport */
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

    /** @var  User */
    protected $user;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->factory = $container->get('oro_channel.builder.factory');
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
            ->createGuestCustomerGroup()
            ->createStore();

        $account        = $this->createAccount();
        $this->setReference('account', $account);
        for ($i = 1; $i <= 5; $i++) {
            $magentoAddress = $this->createMagentoAddress($this->regions['US-AZ'], $this->countries['US']);
            $customer       = $this->createCustomer($i, $account, $magentoAddress);
            $this->setReference('customer_' . $i, $customer);
        }

        $this->setReference('integration', $this->integration);



        $this->em->flush();
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

        $synchronizationSettings = ConfigObject::create(['isTwoWaySyncEnabled' => true]);
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
        $transport = new MagentoSoapTransport();
        $transport->setAdminUrl('http://localhost/magento/admin');
        $transport->setApiKey('key');
        $transport->setApiUser('user');
        $transport->setIsExtensionInstalled(true);
        $transport->setExtensionVersion(SoapTransport::REQUIRED_EXTENSION_VERSION);
        $transport->setMagentoVersion('1.9.1.0');
        $transport->setIsWsiMode(false);
        $transport->setWebsiteId('1');
        $transport->setApiUrl('http://localhost/magento/api/v2_soap?wsdl=1');
        $transport->setWebsites([['id' => 1, 'label' => 'Website ID: 1, Stores: English, French, German']]);

        $this->em->persist($transport);
        $this->transport = $transport;

        return $this;
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
        $address->setPrimary(true);

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
        $customer->setFirstName('John ' . $oid);
        $customer->setLastName('Doe ' . $oid);
        $customer->setEmail('test' . $oid . '@example.com');
        $customer->setOriginId($oid);
        $customer->setIsActive(true);
        $customer->setWebsite($this->website);
        $customer->setStore($this->store);
        $customer->setAccount($account);
        $customer->setGender(Gender::MALE);
        $customer->setGroup($this->customerGroup);
        // DateTimeZones should be removed in BAP-8710. Tests should be passed for:
        //  - Oro\Bundle\MagentoBundle\Tests\Functional\Controller\Api\Rest\CustomerControllerTest
        $customer->setCreatedAt(new \DateTime('now', new \DateTimezone('UTC')));
        $customer->setUpdatedAt(new \DateTime('now', new \DateTimezone('UTC')));
        $customer->addAddress($address);
        $customer->setOwner($this->getUser());
        $customer->setOrganization($this->organization);
        $customerAssociation = new CustomerAssociation();
        $customerAssociation->setTarget($account, $customer);

        $contact = $this->createContact($customer);

        $customer->setContact($contact);

        $this->em->persist($customer);
        $this->em->persist($customerAssociation);

        return $customer;
    }

    /**
     * @param Customer $customer
     *
     * @return Contact
     */
    protected function createContact(Customer $customer)
    {
        $contact = new Contact();
        $contact->setFirstName($customer->getFirstName());
        $contact->setLastName($customer->getLastName());
        $contact->setGender($customer->getGender());
        $contact->setOwner($this->getUser());

        $contact->setOrganization($this->organization);
        $this->em->persist($contact);

        return $contact;
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
     * @return $this
     */
    protected function createGuestCustomerGroup()
    {
        $customerGroup = new CustomerGroup;
        $customerGroup->setName('NOT LOGGED IN');
        $customerGroup->setChannel($this->integration);
        $customerGroup->setOriginId(0);

        $this->em->persist($customerGroup);
        return $this;
    }

    /**
     * @param Order $order
     *
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
        if (!$this->user) {
            $this->user = $this->em->getRepository('OroUserBundle:User')->findOneBy(['username' => 'admin']);
        }

        return $this->user;
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
