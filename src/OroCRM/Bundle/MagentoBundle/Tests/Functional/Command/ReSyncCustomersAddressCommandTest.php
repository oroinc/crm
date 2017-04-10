<?php

namespace OroCRM\Bundle\MagentoBundle\Tests\Functional\Command;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

use OroCRM\Bundle\ContactBundle\Entity\ContactAddress;
use OroCRM\Bundle\MagentoBundle\Entity\Address;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface;
use OroCRM\Bundle\MagentoBundle\Tests\Functional\Fixture\ResyncMagentoCustomerAddresses\LoadMagentoChannel;

/**
 * @dbIsolationPerTest
 */
class ReSyncCustomersAddressCommandTest extends WebTestCase
{
    /** @var MagentoTransportInterface|\PHPUnit_Framework_MockObject_MockObject  */
    protected $transport;

    public function setUp()
    {
        $this->initClient();
        $this->loadFixtures([LoadMagentoChannel::class]);

        $this->transport = $this
            ->getMockBuilder('OroCRM\Bundle\MagentoBundle\Provider\Transport\MagentoTransportInterface')
            ->getMock();
        $this->getContainer()->set('orocrm_magento.transport.soap_transport', $this->transport);
    }

    /**
     * @dataProvider provider
     *
     * @param $magentoCustomerAddresses
     * @param $customerIdentificator
     * @param $expectCountExistingAddress
     * @param $expectCountExistingContactAddress
     * @param $isPrimary
     */
    public function testCommand(
        $magentoCustomerAddresses,
        $customerIdentificator,
        $expectCountExistingAddress,
        $expectCountExistingContactAddress,
        $isPrimary
    ) {
        $entityManager = $this->getContainer()->get('doctrine');
        $customer = $this->getReference($customerIdentificator);
        // init transport
        $this->transport->expects(self::any())->method('getCustomerAddresses')
            ->willReturn($magentoCustomerAddresses);

        // loading existing addresses
        $addressRepository = $entityManager->getRepository('OroCRM\Bundle\MagentoBundle\Entity\Address');
        $addresses = $addressRepository->findBy(['owner'=> $customer]);

        $contactAddressRepository = $entityManager->getRepository('OroCRM\Bundle\ContactBundle\Entity\ContactAddress');
        $contactAddress = $contactAddressRepository->findBy(['owner'=>$customer->getContact()]);

        // Check count
        self::assertEquals($expectCountExistingContactAddress, count($contactAddress));
        self::assertEquals($expectCountExistingAddress, count($addresses));

        $integrationId = $this->getReference('integration')->getId();

        $this->runCommand('oro:magento:re-sync-customer-address', [
            '--integration-id=' . $integrationId,
            '--id='.$customer->getID(),
            '--batch-size=2'
        ]);

        $addresses = $addressRepository->findBy(['owner'=> $customer]);
        self::assertEquals(1, count($addresses));

        $address = current($addresses);
        $expectedData = reset($magentoCustomerAddresses);
        $expectedData['owner_id'] = $customer->getID();
        $expectedData['integrationId'] = $integrationId;
        $expectedData['isPrimary'] = $isPrimary;

        $this->assertCustomerAddress($expectedData, current($addresses), $customer);
        $this->assertContactAddress($expectedData, $address->getContactAddress(), $customer);
    }

    /**
     * @param $expectedData
     * @param Address $address
     */
    protected function assertCustomerAddress($expectedData, Address $address, Customer $customer)
    {
        self::assertEquals($expectedData['firstname'], $address->getFirstName());
        self::assertEquals($expectedData['lastname'], $address->getLastName());
        self::assertEquals($expectedData['country_id'], $address->getCountry()->getIso2Code());
        self::assertEquals($expectedData['company'], $address->getOrganization());
        self::assertEquals($expectedData['city'], $address->getCity());
        self::assertEquals($expectedData['postcode'], $address->getPostalCode());
        self::assertEquals($expectedData['street'], $address->getStreet());
        self::assertEquals($expectedData['telephone'], $address->getPhone());
        self::assertEquals($expectedData['region'], $address->getRegionName());
        self::assertEquals($expectedData['owner_id'], $address->getOwner()->getId());
        self::assertEquals($expectedData['integrationId'], $address->getChannel()->getId());
        self::assertEquals($expectedData['region'], $address->getRegionText());
        self::assertEquals($expectedData['isPrimary'], $address->isPrimary());
        self::assertEquals($customer->getId(), $address->getOwner()->getId());
    }

    /**
     * @param $expectedData
     * @param ContactAddress $contactAddress
     * @param Customer $customer
     */
    protected function assertContactAddress($expectedData, ContactAddress $contactAddress, Customer $customer)
    {
        self::assertEquals($expectedData['firstname'], $contactAddress->getFirstName());
        self::assertEquals($expectedData['lastname'], $contactAddress->getLastName());
        self::assertEquals($expectedData['country_id'], $contactAddress->getCountry()->getIso2Code());
        self::assertEquals($expectedData['company'], $contactAddress->getOrganization());
        self::assertEquals($expectedData['city'], $contactAddress->getCity());
        self::assertEquals($expectedData['postcode'], $contactAddress->getPostalCode());
        self::assertEquals($expectedData['street'], $contactAddress->getStreet());
        self::assertEquals($expectedData['region'], $contactAddress->getRegionName());
        self::assertEquals($expectedData['isPrimary'], $contactAddress->isPrimary());
        self::assertEquals($customer->getContact()->getId(), $contactAddress->getOwner()->getId());
    }

    /**
     * @return array
     */
    public function provider()
    {
        return [
            'testCommandWithoutAddressAndWithoutContactAddress' => [
                '$magentoCustomerAddresses' => $this->getMagentoCustomerAddresses(),
                'customer_without_address_and_contactAddress' => 'customer_without_address_and_contactAddress',
                '$expectCountExistingAddress' => 0,
                '$expectCountExistingContactAddress' => 0,
                '$isPrimary' => true // there is primary true because new address will be created
            ],
            'testCommandWithAddressAndWithoutContactAddress' => [
                '$magentoCustomerAddresses' => $this->getMagentoCustomerAddressesUpdated(),
                'customer_without_contactAddress' =>'customer_without_contactAddress',
                '$expectCountExistingAddress' => 1,
                '$expectCountExistingContactAddress' => 0,
                '$isPrimary' => true // there is primary true because existing address is primary
            ],
            'testCommandWithAddressAndWithContactAddress' => [
                '$magentoCustomerAddresses' => $this->getMagentoCustomerAddressesUpdated(),
                'customer_with_address_and_contactAddress' => 'customer_with_address_and_contactAddress',
                '$expectCountExistingAddress' => 1,
                '$expectCountExistingContactAddress' => 1,
                '$isPrimary' => true // there is primary true because existing address is primary
            ]
        ];
    }

    /**
     * @return array
     */
    protected function getMagentoCustomerAddressesUpdated()
    {
        return [
            [
                'customer_address_id' => 1,
                'created_at' => (new \DateTime())->format('Y-m-d H:i'),
                'updated_at' => (new \DateTime())->format('Y-m-d H:i'),
                'city' => 'LA1',
                'company' => 'Oro1',
                'country_id' => 'US',
                'firstname' => 'John1',
                'lastname' => 'Smith1',
                'postcode' => '900461',
                'region' => 'California1',
                'region_id' => 13,
                'street' => 'Melrose Ave.1',
                'telephone' => '0011',
                'is_default_billing' => true,
                'is_default_shipping' => true,
                'attributes' => [
                    [
                        'key' => 'parent_id',
                        'value' => '194'
                    ],
                    [
                        'key' => 'entity_type_id',
                        'value' => '2'
                    ],
                    [
                        'key' => 'attribute_set_id',
                        'value' => '0'
                    ],
                    [
                        'key' => 'is_active',
                        'value' => '1'
                    ],
                    [
                        'key' => 'customer_id',
                        'value' => '194'
                    ]
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    protected function getMagentoCustomerAddresses()
    {
        return [
            [
                'customer_address_id' => 1,
                'created_at' => (new \DateTime())->format('Y-m-d H:i'),
                'updated_at' => (new \DateTime())->format('Y-m-d H:i'),
                'city' => 'LA',
                'company' => 'Oro',
                'country_id' => 'US',
                'firstname' => 'John',
                'lastname' => 'Smith',
                'postcode' => '90046',
                'region' => 'California',
                'region_id' => 12,
                'street' => 'Melrose Ave.',
                'telephone' => '001',
                'is_default_billing' => true,
                'is_default_shipping' => true,
                'attributes' => [
                    [
                        'key'=>'parent_id',
                        'value'=>'194'
                    ],
                    [
                        'key'=>'entity_type_id',
                        'value'=>'2'
                    ],
                    [
                        'key'=>'attribute_set_id',
                        'value'=>'0'
                    ],
                    [
                        'key'=>'is_active',
                        'value'=>'1'
                    ],
                    [
                        'key'=>'customer_id',
                        'value'=>'194'
                    ]
                ]
            ]
        ];
    }
}
