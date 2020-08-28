<?php

namespace Oro\Bundle\ContactBundle\Tests\Functional\Api\RestJsonApi;

use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\AddressBundle\Tests\Functional\Api\RestJsonApi\AddressCountryAndRegionTestTrait;
use Oro\Bundle\AddressBundle\Tests\Functional\Api\RestJsonApi\PrimaryAddressTestTrait;
use Oro\Bundle\AddressBundle\Tests\Functional\Api\RestJsonApi\UnchangeableAddressOwnerTestTrait;
use Oro\Bundle\ApiBundle\Tests\Functional\RestJsonApiTestCase;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Entity\ContactAddress;

/**
 * @dbIsolationPerTest
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class ContactAddressTest extends RestJsonApiTestCase
{
    use AddressCountryAndRegionTestTrait;
    use PrimaryAddressTestTrait;
    use UnchangeableAddressOwnerTestTrait;

    private const ENTITY_CLASS                  = ContactAddress::class;
    private const ENTITY_TYPE                   = 'contactaddresses';
    private const OWNER_ENTITY_TYPE             = 'contacts';
    private const OWNER_RELATIONSHIP            = 'owner';
    private const CREATE_MIN_REQUEST_DATA       = 'create_contact_address_min.yml';
    private const OWNER_CREATE_MIN_REQUEST_DATA = 'create_contact_min.yml';
    private const IS_REGION_REQUIRED            = false;
    private const COUNTRY_REGION_ADDRESS_REF    = 'contact_address1';
    private const PRIMARY_ADDRESS_REF           = 'contact_address1';
    private const UNCHANGEABLE_ADDRESS_REF      = 'contact_address1';
    private const OWNER_REF                     = 'contact1';
    private const ANOTHER_OWNER_REF             = 'another_contact';
    private const ANOTHER_OWNER_ADDRESS_2_REF   = 'another_contact_address2';

    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures(['@OroContactBundle/Tests/Functional/Api/DataFixtures/contact_addresses.yml']);
    }

    /**
     * @param ContactAddress $address
     *
     * @return Contact
     */
    private function getOwner(ContactAddress $address)
    {
        return $address->getOwner();
    }

    public function testGetList()
    {
        $response = $this->cget(
            ['entity' => self::ENTITY_TYPE]
        );

        $this->assertResponseContains('cget_contact_address.yml', $response);
    }

    public function testGetListFilterByCountry()
    {
        $response = $this->cget(
            ['entity' => self::ENTITY_TYPE],
            ['filter' => ['country' => '<toString(@country_israel->iso2Code)>']]
        );

        $this->assertResponseContains('cget_contact_address_filter_country.yml', $response);
    }

    public function testGetListFilterByRegion()
    {
        $response = $this->cget(
            ['entity' => self::ENTITY_TYPE],
            ['filter' => ['region' => '<toString(@region_israel_telaviv->combinedCode)>']]
        );

        $this->assertResponseContains('cget_contact_address_filter_region.yml', $response);
    }

    public function testGet()
    {
        $response = $this->get(
            ['entity' => self::ENTITY_TYPE, 'id' => '<toString(@contact_address2->id)>']
        );

        $this->assertResponseContains('get_contact_address.yml', $response);
    }

    public function testCreate()
    {
        $contactId = $this->getReference('contact1')->getId();
        $countryId = $this->getReference('country_usa')->getIso2Code();
        $regionId = $this->getReference('region_usa_california')->getCombinedCode();

        $response = $this->post(
            ['entity' => self::ENTITY_TYPE],
            'create_contact_address.yml'
        );

        $addressId = (int)$this->getResourceId($response);
        $responseContent = $this->updateResponseContent('create_contact_address.yml', $response);
        $this->assertResponseContains($responseContent, $response);

        /** @var ContactAddress $address */
        $address = $this->getEntityManager()
            ->find(self::ENTITY_CLASS, $addressId);
        self::assertNotNull($address);
        self::assertEquals('New Address', $address->getLabel());
        self::assertEquals('Street 1', $address->getStreet());
        self::assertEquals('Street 2', $address->getStreet2());
        self::assertEquals('Los Angeles', $address->getCity());
        self::assertEquals('90001', $address->getPostalCode());
        self::assertEquals('Acme', $address->getOrganization());
        self::assertEquals('Mr.', $address->getNamePrefix());
        self::assertEquals('M.D.', $address->getNameSuffix());
        self::assertEquals('John', $address->getFirstName());
        self::assertEquals('Edgar', $address->getMiddleName());
        self::assertEquals('Doo', $address->getLastName());
        self::assertEquals($contactId, $address->getOwner()->getId());
        self::assertEquals($countryId, $address->getCountry()->getIso2Code());
        self::assertEquals($regionId, $address->getRegion()->getCombinedCode());
    }

    public function testCreateWithRequiredDataOnly()
    {
        $contactId = $this->getReference('contact1')->getId();
        $countryId = $this->getReference('country_usa')->getIso2Code();

        $data = $this->getRequestData(self::CREATE_MIN_REQUEST_DATA);
        $response = $this->post(
            ['entity' => self::ENTITY_TYPE],
            $data
        );

        $addressId = (int)$this->getResourceId($response);
        $responseContent = $data;
        $responseContent['data']['attributes']['label'] = null;
        $responseContent['data']['attributes']['street'] = null;
        $responseContent['data']['attributes']['street2'] = null;
        $responseContent['data']['attributes']['city'] = null;
        $responseContent['data']['attributes']['postalCode'] = null;
        $responseContent['data']['attributes']['organization'] = null;
        $responseContent['data']['attributes']['namePrefix'] = null;
        $responseContent['data']['attributes']['nameSuffix'] = null;
        $responseContent['data']['attributes']['firstName'] = null;
        $responseContent['data']['attributes']['middleName'] = null;
        $responseContent['data']['attributes']['lastName'] = null;
        $responseContent['data']['relationships']['region']['data'] = null;
        $this->assertResponseContains($responseContent, $response);

        /** @var ContactAddress $address */
        $address = $this->getEntityManager()
            ->find(self::ENTITY_CLASS, $addressId);
        self::assertNotNull($address);
        self::assertNull($address->getLabel());
        self::assertNull($address->getStreet());
        self::assertNull($address->getStreet2());
        self::assertNull($address->getCity());
        self::assertNull($address->getPostalCode());
        self::assertNull($address->getOrganization());
        self::assertNull($address->getNamePrefix());
        self::assertNull($address->getNameSuffix());
        self::assertNull($address->getFirstName());
        self::assertNull($address->getMiddleName());
        self::assertNull($address->getLastName());
        self::assertEquals($contactId, $address->getOwner()->getId());
        self::assertEquals($countryId, $address->getCountry()->getIso2Code());
        self::assertTrue(null === $address->getRegion());
    }

    public function testCreateWithCustomFields()
    {
        $data = $this->getRequestData(self::CREATE_MIN_REQUEST_DATA);
        $data['data']['attributes']['customField1'] = 'custom field 1 value';
        $data['data']['attributes']['custom_field_2'] = 'custom field 2 value';
        $response = $this->post(
            ['entity' => self::ENTITY_TYPE],
            $data
        );

        $addressId = (int)$this->getResourceId($response);
        $this->assertResponseContains($data, $response);

        /** @var ContactAddress $address */
        $address = $this->getEntityManager()
            ->find(self::ENTITY_CLASS, $addressId);
        self::assertNotNull($address);
        self::assertEquals($data['data']['attributes']['customField1'], $address->getCustomField1());
        self::assertEquals($data['data']['attributes']['custom_field_2'], $address->getCustomField2());
    }

    public function testUpdate()
    {
        $addressId = $this->getReference('contact_address2')->getId();
        $data = [
            'data' => [
                'type'       => self::ENTITY_TYPE,
                'id'         => (string)$addressId,
                'attributes' => [
                    'label' => 'Updated Address'
                ]
            ]
        ];

        $response = $this->patch(
            ['entity' => self::ENTITY_TYPE, 'id' => (string)$addressId],
            $data
        );

        $this->assertResponseContains($data, $response);

        /** @var ContactAddress $address */
        $address = $this->getEntityManager()
            ->find(self::ENTITY_CLASS, $addressId);
        self::assertNotNull($address);
        self::assertEquals('Updated Address', $address->getLabel());
    }

    public function testUpdateCustomFields()
    {
        $addressId = $this->getReference('contact_address2')->getId();
        $data = [
            'data' => [
                'type'       => self::ENTITY_TYPE,
                'id'         => (string)$addressId,
                'attributes' => [
                    'customField1'   => 'Updated Value 1',
                    'custom_field_2' => 'Updated Value 2'
                ]
            ]
        ];

        $response = $this->patch(
            ['entity' => self::ENTITY_TYPE, 'id' => (string)$addressId],
            $data
        );

        $this->assertResponseContains($data, $response);

        /** @var ContactAddress $address */
        $address = $this->getEntityManager()
            ->find(self::ENTITY_CLASS, $addressId);
        self::assertNotNull($address);
        self::assertEquals($data['data']['attributes']['customField1'], $address->getCustomField1());
        self::assertEquals($data['data']['attributes']['custom_field_2'], $address->getCustomField2());
    }

    public function testDelete()
    {
        $addressId = $this->getReference('contact_address2')->getId();

        $this->delete(
            ['entity' => self::ENTITY_TYPE, 'id' => (string)$addressId]
        );

        $address = $this->getEntityManager()
            ->find(self::ENTITY_CLASS, $addressId);
        self::assertTrue(null === $address);
    }

    public function testDeleteList()
    {
        $addressId = $this->getReference('contact_address2')->getId();

        $this->cdelete(
            ['entity' => self::ENTITY_TYPE],
            ['filter' => ['id' => (string)$addressId]]
        );

        $address = $this->getEntityManager()
            ->find(self::ENTITY_CLASS, $addressId);
        self::assertTrue(null === $address);
    }

    public function testTryToSetNullCountry()
    {
        $addressId = $this->getReference('contact_address1')->getId();
        $data = [
            'data' => [
                'type'          => self::ENTITY_TYPE,
                'id'            => (string)$addressId,
                'relationships' => [
                    'country' => [
                        'data' => null
                    ]
                ]
            ]
        ];
        $response = $this->patch(
            ['entity' => self::ENTITY_TYPE, 'id' => $addressId],
            $data,
            [],
            false
        );

        $this->assertResponseValidationError(
            [
                'title'  => 'not blank constraint',
                'detail' => 'This value should not be blank.',
                'source' => ['pointer' => '/data/relationships/country/data']
            ],
            $response
        );
    }

    public function testTryToUpdateOwner()
    {
        /** @var ContactAddress $address */
        $address = $this->getReference('contact_address1');
        $addressId = $address->getId();
        $ownerId = $address->getOwner()->getId();
        $data = [
            'data' => [
                'type'          => self::ENTITY_TYPE,
                'id'            => (string)$addressId,
                'relationships' => [
                    'owner' => [
                        'data' => [
                            'type' => 'contacts',
                            'id'   => '<toString(@another_contact->id)>'
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->patch(
            ['entity' => self::ENTITY_TYPE, 'id' => (string)$addressId],
            $data
        );

        $data['data']['relationships']['owner']['data']['id'] = (string)$ownerId;
        $this->assertResponseContains($data, $response);
        self::assertSame(
            $ownerId,
            $this->getEntityManager()->find(self::ENTITY_CLASS, $addressId)->getOwner()->getId()
        );
    }

    public function testTryToUpdateOwnerViaRelationship()
    {
        $addressId = $this->getReference('contact_address1')->getId();
        $data = [
            'data' => [
                'type' => 'contacts',
                'id'   => '<toString(@contact1->id)>'
            ]
        ];

        $response = $this->patchRelationship(
            ['entity' => self::ENTITY_TYPE, 'id' => (string)$addressId, 'association' => 'owner'],
            $data,
            [],
            false
        );

        self::assertMethodNotAllowedResponse($response, 'OPTIONS, GET');
    }

    public function testCreateOneMoreShippingAddress()
    {
        /** @var ContactAddress $existingAddress */
        $existingAddress = $this->getReference('contact_address2');
        $existingAddressId = $existingAddress->getId();
        /** @var Contact $owner */
        $owner = $existingAddress->getOwner();
        $ownerId = $owner->getId();

        // guard
        self::assertCount(3, $owner->getAddresses());
        self::assertTrue($existingAddress->hasTypeWithName(AddressType::TYPE_SHIPPING));

        $data = $this->getRequestData(self::CREATE_MIN_REQUEST_DATA);
        $data['data']['relationships']['owner']['data']['id'] = (string)$ownerId;
        $data['data']['relationships']['types']['data'] = [
            ['type' => 'addresstypes', 'id' => '<toString(@shipping->name)>']
        ];
        $response = $this->post(
            ['entity' => self::ENTITY_TYPE],
            $data
        );

        /** @var ContactAddress $newAddress */
        $newAddress = $this->getEntityManager()
            ->find(self::ENTITY_CLASS, (int)$this->getResourceId($response));
        self::assertTrue($newAddress->hasTypeWithName(AddressType::TYPE_SHIPPING));
        /** @var ContactAddress $existingAddress */
        $existingAddress = $this->getEntityManager()
            ->find(self::ENTITY_CLASS, $existingAddressId);
        self::assertFalse($existingAddress->hasTypeWithName(AddressType::TYPE_SHIPPING));
    }

    public function testCreateOneMoreShippingAddressViaContactUpdateResource()
    {
        /** @var ContactAddress $existingAddress */
        $existingAddress = $this->getReference('contact_address2');
        $existingAddressId = $existingAddress->getId();
        /** @var Contact $owner */
        $owner = $existingAddress->getOwner();
        $ownerId = $owner->getId();

        // guard
        self::assertCount(3, $owner->getAddresses());
        self::assertTrue($existingAddress->hasTypeWithName(AddressType::TYPE_SHIPPING));

        $addressData = $this->getRequestData(self::CREATE_MIN_REQUEST_DATA);
        $addressData['data']['id'] = 'new_address';
        $addressData['data']['relationships']['owner']['data']['id'] = (string)$ownerId;
        $addressData['data']['relationships']['types']['data'] = [
            ['type' => 'addresstypes', 'id' => '<toString(@shipping->name)>']
        ];
        $data = [
            'data'     => [
                'type'          => 'contacts',
                'id'            => (string)$ownerId,
                'relationships' => [
                    'addresses' => [
                        'data' => [
                            ['type' => self::ENTITY_TYPE, 'id' => (string)$existingAddressId],
                            ['type' => self::ENTITY_TYPE, 'id' => 'new_address']
                        ]
                    ]
                ]
            ],
            'included' => [
                $addressData['data']
            ]
        ];
        $response = $this->patch(
            ['entity' => 'contacts', 'id' => (string)$ownerId],
            $data
        );

        /** @var ContactAddress $newAddress */
        $newAddress = $this->getEntityManager()
            ->find(self::ENTITY_CLASS, self::getNewResourceIdFromIncludedSection($response, 'new_address'));
        self::assertTrue($newAddress->hasTypeWithName(AddressType::TYPE_SHIPPING));
        /** @var ContactAddress $existingAddress */
        $existingAddress = $this->getEntityManager()
            ->find(self::ENTITY_CLASS, $existingAddressId);
        self::assertFalse($existingAddress->hasTypeWithName(AddressType::TYPE_SHIPPING));
    }

    public function testCreateSeveralShippingAddressesWithContactRelationshipViaContactUpdateResource()
    {
        /** @var Contact $owner */
        $owner = $this->getReference('contact_address1')->getOwner();
        $ownerId = $owner->getId();
        $owner->getAddresses()->clear();
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();

        // guard
        self::assertCount(0, $owner->getAddresses());

        $addressData1 = $this->getRequestData(self::CREATE_MIN_REQUEST_DATA);
        $addressData1['data']['id'] = 'new_address1';
        $addressData1['data']['relationships']['owner']['data']['id'] = (string)$ownerId;
        $addressData1['data']['relationships']['types']['data'] = [
            ['type' => 'addresstypes', 'id' => '<toString(@shipping->name)>']
        ];
        $addressData2 = $this->getRequestData(self::CREATE_MIN_REQUEST_DATA);
        $addressData2['data']['id'] = 'new_address2';
        $addressData2['data']['relationships']['owner']['data']['id'] = (string)$ownerId;
        $addressData2['data']['relationships']['types']['data'] = [
            ['type' => 'addresstypes', 'id' => '<toString(@shipping->name)>'],
            ['type' => 'addresstypes', 'id' => '<toString(@billing->name)>']
        ];
        $data = [
            'data'     => [
                'type'          => 'contacts',
                'id'            => (string)$ownerId,
                'relationships' => [
                    'addresses' => [
                        'data' => [
                            ['type' => self::ENTITY_TYPE, 'id' => 'new_address1'],
                            ['type' => self::ENTITY_TYPE, 'id' => 'new_address2']
                        ]
                    ]
                ]
            ],
            'included' => [
                $addressData1['data'],
                $addressData2['data']
            ]
        ];
        $response = $this->patch(
            ['entity' => 'contacts', 'id' => (string)$ownerId],
            $data
        );

        /** @var ContactAddress $newAddress1 */
        $newAddress1 = $this->getEntityManager()
            ->find(self::ENTITY_CLASS, self::getNewResourceIdFromIncludedSection($response, 'new_address1'));
        self::assertTrue($newAddress1->hasTypeWithName(AddressType::TYPE_SHIPPING));
        /** @var ContactAddress $newAddress2 */
        $newAddress2 = $this->getEntityManager()
            ->find(self::ENTITY_CLASS, self::getNewResourceIdFromIncludedSection($response, 'new_address2'));
        self::assertFalse($newAddress2->hasTypeWithName(AddressType::TYPE_SHIPPING));
        self::assertTrue($newAddress2->hasTypeWithName(AddressType::TYPE_BILLING));
    }

    public function testCreateSeveralShippingAddressesWithoutContactRelationshipViaContactUpdateResource()
    {
        /** @var Contact $owner */
        $owner = $this->getReference('contact_address1')->getOwner();
        $ownerId = $owner->getId();
        $owner->getAddresses()->clear();
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();

        // guard
        self::assertCount(0, $owner->getAddresses());

        $addressData1 = $this->getRequestData(self::CREATE_MIN_REQUEST_DATA);
        $addressData1['data']['id'] = 'new_address1';
        unset($addressData1['data']['relationships']['owner']);
        $addressData1['data']['relationships']['types']['data'] = [
            ['type' => 'addresstypes', 'id' => '<toString(@shipping->name)>']
        ];
        $addressData2 = $this->getRequestData(self::CREATE_MIN_REQUEST_DATA);
        $addressData2['data']['id'] = 'new_address2';
        unset($addressData2['data']['relationships']['owner']);
        $addressData2['data']['relationships']['types']['data'] = [
            ['type' => 'addresstypes', 'id' => '<toString(@shipping->name)>'],
            ['type' => 'addresstypes', 'id' => '<toString(@billing->name)>']
        ];
        $data = [
            'data'     => [
                'type'          => 'contacts',
                'id'            => (string)$ownerId,
                'relationships' => [
                    'addresses' => [
                        'data' => [
                            ['type' => self::ENTITY_TYPE, 'id' => 'new_address1'],
                            ['type' => self::ENTITY_TYPE, 'id' => 'new_address2']
                        ]
                    ]
                ]
            ],
            'included' => [
                $addressData1['data'],
                $addressData2['data']
            ]
        ];
        $response = $this->patch(
            ['entity' => 'contacts', 'id' => (string)$ownerId],
            $data
        );

        /** @var ContactAddress $newAddress1 */
        $newAddress1 = $this->getEntityManager()
            ->find(self::ENTITY_CLASS, self::getNewResourceIdFromIncludedSection($response, 'new_address1'));
        self::assertTrue($newAddress1->hasTypeWithName(AddressType::TYPE_SHIPPING));
        /** @var ContactAddress $newAddress2 */
        $newAddress2 = $this->getEntityManager()
            ->find(self::ENTITY_CLASS, self::getNewResourceIdFromIncludedSection($response, 'new_address2'));
        self::assertFalse($newAddress2->hasTypeWithName(AddressType::TYPE_SHIPPING));
        self::assertTrue($newAddress2->hasTypeWithName(AddressType::TYPE_BILLING));
    }
}
