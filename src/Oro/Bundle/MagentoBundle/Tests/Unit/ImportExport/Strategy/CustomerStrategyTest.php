<?php

namespace Oro\Bundle\MagentoBundle\Tests\Unit\ImportExport\Strategy;

use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\MagentoBundle\Entity\Address;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\MagentoBundle\Entity\CustomerGroup;
use Oro\Bundle\MagentoBundle\Entity\Store;
use Oro\Bundle\MagentoBundle\Entity\Website;
use Oro\Bundle\MagentoBundle\ImportExport\Strategy\CustomerStrategy;
use Oro\Bundle\OrganizationBundle\Ownership\EntityOwnershipAssociationsSetter;

class CustomerStrategyTest extends AbstractStrategyTest
{
    /**
     * @return CustomerStrategy
     */
    protected function getStrategy()
    {
        $strategy = new CustomerStrategy(
            $this->eventDispatcher,
            $this->strategyHelper,
            $this->fieldHelper,
            $this->databaseHelper,
            $this->chainEntityClassNameProvider,
            $this->translator,
            $this->newEntitiesHelper,
            $this->doctrineHelper,
            $this->relatedEntityStateHelper
        );

        $strategy->setOwnerHelper($this->defaultOwnerHelper);
        $strategy->setChannelHelper($this->channelHelper);
        $strategy->setAddressHelper($this->addressHelper);

        $this->databaseHelper->expects($this->any())->method('getEntityReference')
            ->will($this->returnArgument(0));

        $strategy->setImportExportContext(
            $this->getMockBuilder('Oro\Bundle\ImportExportBundle\Context\ContextInterface')
            ->disableOriginalConstructor()
            ->getMock()
        );
        $strategy->setEntityName('Oro\Bundle\MagentoBundle\Entity\Customer');

        $execution = $this->getMockBuilder('Akeneo\Bundle\BatchBundle\Item\ExecutionContext')->getMock();
        $this->jobExecution->expects($this->any())->method('getExecutionContext')
            ->will($this->returnValue($execution));
        $strategy->setStepExecution($this->stepExecution);
        $strategy->setOwnershipSetter($this->createMock(EntityOwnershipAssociationsSetter::class));

        return $strategy;
    }

    public function testProcessExistingCustomerWithStore()
    {
        $website = $this->getWebsite();

        $store = new Store();
        $store->setWebsite($website);

        $customerEmail = 'test@example.com';
        $channel = new Channel();
        $channel->setName('test_channel');

        $customer = $this->getCustomer();
        $customer->setChannel($channel);
        $customer->setStore($store);
        $customer->setWebsite($website);
        $customer->setEmail($customerEmail);

        $this->databaseHelper->expects($this->any())
            ->method('findOneBy')
            ->will(
                $this->returnValueMap(
                    [
                        [
                            'Oro\Bundle\MagentoBundle\Entity\Customer',
                            [
                                'email' => $customerEmail,
                                'channel' => $channel
                            ],
                            $customer
                        ]
                    ]
                )
            );

        $address = $this->getAddress();
        $customer->addAddress($address);

        /** @var  $strategy */
        $strategy = $this->getStrategy();
        /** @var Customer $result */
        $result = $strategy->process($customer);
        $this->assertNotEmpty($result);
        $this->assertFalse($result->isGuest());
    }

    public function testProcessNewRegisteredCustomerWithStore()
    {
        $website = $this->getWebsite();

        $store = new Store();
        $store->setWebsite($website);

        $customer = $this->getCustomer();
        $customer->setChannel(new Channel());
        $customer->setGuest(false);
        $customer->setEmail('test@example.com');
        $customer->setStore($store);
        $customer->setWebsite($website);

        $group = new CustomerGroup();
        $group->setId(1);
        $customer->setGroup($group);

        /** @var Customer $result */
        $result = $this->getStrategy()->process($customer);

        $this->assertNotEmpty($result);
        $this->assertEquals($result->getGroup(), $group);
        $this->assertFalse($result->isGuest());
    }

    /**
     * Process new Magento registered customer
     */
    public function testProcessNewRegisteredCustomerWithAddress()
    {
        $website = $this->getWebsite();

        $store = new Store();
        $store->setWebsite($website);

        $group = new CustomerGroup();
        $group->setId(1);

        $address = $this->getAddress();

        $customer = $this->getCustomer();
        $customer->setChannel(new Channel());
        $customer->setGuest(false);
        $customer->setEmail('test@example.com');
        $customer->setStore($store);
        $customer->setWebsite($website);
        $customer->setGroup($group);
        $customer->addAddress($address);

        /** @var Customer $result */
        $result = $this->getStrategy()->process($customer);

        $this->assertNotEmpty($result);
        $this->assertEquals($result->getGroup(), $group);
        $this->assertFalse($result->isGuest());
    }

    /**
     * Process customer that created orders as a guest and registered in Magento
     * Should be merged with guest
     */
    public function testProcessRegisteredCustomerMergeWithExistingGuest()
    {
        $website = $this->getWebsite();

        $store = new Store();
        $store->setWebsite($website);

        $channel = new Channel();
        $channel->setName('test_channel');
        $email = 'test@example.com';

        $guestCustomer = new Customer();
        $guestCustomer->setId(1);
        $guestCustomer->setGuest(true);
        $guestCustomer->setStore($store);
        $guestCustomer->setEmail($email);
        $notLoggedIdGroup = new CustomerGroup();
        $notLoggedIdGroup->setId(0);
        $guestCustomer->setGroup($notLoggedIdGroup);
        $guestCustomer->setChannel($channel);

        $customer = $this->getCustomer();
        $customer->setChannel($channel);
        $customer->setGuest(false);
        $customer->setStore($store);
        $customer->setWebsite($website);
        $customer->setEmail($email);
        $group = new CustomerGroup();
        $group->setId(1);
        $customer->setGroup($group);

        $address = $this->getAddress();
        $customer->addAddress($address);

        $this->databaseHelper->expects($this->any())
            ->method('findOneBy')
            ->will(
                $this->returnValueMap(
                    [
                        [
                            'Oro\Bundle\MagentoBundle\Entity\Website',
                            [
                                'originId' => 1,
                                'channel' => $channel
                            ],
                            $website
                        ],
                        [
                            'Oro\Bundle\MagentoBundle\Entity\Customer',
                            [
                                'email' => $email,
                                'channel' => $channel,
                                'website' => $website,
                                'originId' => null

                            ],
                            $guestCustomer
                        ]
                    ]
                )
            );

        /** @var Customer $result */
        $result = $this->getStrategy()->process($customer);

        $this->assertNotEmpty($result);
        $this->assertTrue($result->getIsActive());
        $this->assertFalse($result->isGuest());
    }

    /**
     * @return Customer
     */
    protected function getCustomer()
    {
        $customer = new Customer();
        $customer->setId(1);
        $customer->setOriginId(1);

        return $customer;
    }

    /**
     * @return Website
     */
    protected function getWebsite()
    {
        $website = new Website();
        $website->setId(1);
        $website->setOriginId(1);

        return $website;
    }

    /**
     * @return Address
     */
    protected function getAddress()
    {
        $region = new Region('US-NY');
        $region->setCode('State');

        $country = new Country('US');
        $address = new Address();

        $address->setFirstName('First Name')
            ->setLastName('Last Name')
            ->setStreet('Street')
            ->setStreet2('Street2')
            ->setCity('City')
            ->setRegion($region)
            ->setPostalCode('Zip Code')
            ->setCountry($country);
        $address->setCountryText('Test');

        return $address;
    }
}
