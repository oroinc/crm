<?php

namespace Oro\Bundle\MagentoBundle\Tests\Functional\Fixtures;

use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\MagentoBundle\Entity\Address as MagentoAddress;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Entity\MagentoSoapTransport;
use Oro\Bundle\MagentoBundle\Provider\Transport\SoapTransport;
use Oro\Bundle\MagentoBundle\Tests\Functional\Fixture\LoadMagentoChannel;
use Oro\Bundle\SalesBundle\Entity\Customer as CustomerAssociation;
use Oro\Bundle\UserBundle\Model\Gender;

class LoadGuestCustomerStrategyData extends LoadMagentoChannel
{
    const TEST_SHARED_EMAIL     = 'test@example.com';
    const JOHN_DOE_SHARED_EMAIL = 'johndoe@example.com';
    const NONE_SHARED_EMAIL     = 'noneshared@example.com';

    const CUSTOMER_2_ALIAS_REFERENCE_NAME = 'customer2';
    const CUSTOMER_3_ALIAS_REFERENCE_NAME = 'customer3';
    const CUSTOMER_4_ALIAS_REFERENCE_NAME = 'customer4';

    public function load(ObjectManager $manager)
    {
        $this->em        = $manager;
        $this->countries = $this->loadStructure('OroAddressBundle:Country', 'getIso2Code');
        $this->regions   = $this->loadStructure('OroAddressBundle:Region', 'getCombinedCode');
        $this->organization = $manager->getRepository('OroOrganizationBundle:Organization')->getFirst();

        $this
            ->createTransport($this->getSharedEmailList())
            ->createIntegration()
            ->createChannel()
            ->createWebSite()
            ->createCustomerGroup()
            ->createGuestCustomerGroup()
            ->createStore();

        $magentoAddressUSAZ = $this->createMagentoAddress($this->regions['US-AZ'], $this->countries['US']);
        $magentoAddressUSLA = $this->createMagentoAddress($this->regions['US-LA'], $this->countries['US']);
        $account         = $this->createAccount();

        $customer = $this->createCustomer(1, $account, $magentoAddressUSAZ, self::NONE_SHARED_EMAIL);
        $customer2 = $this->createCustomer(2, $account, $magentoAddressUSLA, self::TEST_SHARED_EMAIL);

        $this->setReference(self::CUSTOMER_ALIAS_REFERENCE_NAME, $customer);
        $this->setReference(self::CUSTOMER_2_ALIAS_REFERENCE_NAME, $customer2);
        $this->setReference(self::INTEGRATION_ALIAS_REFERENCE_NAME, $this->integration);

        $this->em->flush();
    }

    /**
     * @param string[] $sharedEmailList
     * @return LoadMagentoChannel
     */
    protected function createTransport($sharedEmailList = [])
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
        $transport->setSharedGuestEmailList($sharedEmailList);

        $this->em->persist($transport);
        $this->transport = $transport;

        return $this;
    }

    /**
     * @param string $name
     *
     * @return Account
     */
    protected function createAccount($name = 'acc')
    {
        $account = new Account;
        $account->setName($name);
        $account->setOwner($this->getUser());
        $account->setOrganization($this->organization);

        $this->em->persist($account);

        return $account;
    }

    /**
     * @param                $oid
     * @param Account        $account
     * @param MagentoAddress $address
     * @param string         $email
     *
     * @return Customer
     */
    protected function createCustomer($oid, Account $account, MagentoAddress $address, $email = 'test@example.com')
    {
        $customer = new Customer();
        $customer->setChannel($this->integration);
        $customer->setDataChannel($this->channel);
        $customer->setFirstName('John');
        $customer->setLastName('Doe');
        $customer->setEmail($email);
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

        $this->em->persist($customer);
        $this->em->persist($customerAssociation);

        return $customer;
    }

    /**
     * @return string[]
     */
    private function getSharedEmailList()
    {
        return [
            self::JOHN_DOE_SHARED_EMAIL,
            self::TEST_SHARED_EMAIL
        ];
    }
}
