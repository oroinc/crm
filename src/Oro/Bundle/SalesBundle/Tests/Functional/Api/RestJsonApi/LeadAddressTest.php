<?php

namespace Oro\Bundle\SalesBundle\Tests\Functional\Api\RestJsonApi;

use Oro\Bundle\AddressBundle\Tests\Functional\Api\RestJsonApi\AddressCountryAndRegionTestTrait;
use Oro\Bundle\AddressBundle\Tests\Functional\Api\RestJsonApi\PrimaryAddressTestTrait;
use Oro\Bundle\AddressBundle\Tests\Functional\Api\RestJsonApi\UnchangeableAddressOwnerTestTrait;
use Oro\Bundle\ApiBundle\Tests\Functional\RestJsonApiTestCase;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SalesBundle\Entity\LeadAddress;

/**
 * @dbIsolationPerTest
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class LeadAddressTest extends RestJsonApiTestCase
{
    use AddressCountryAndRegionTestTrait;
    use PrimaryAddressTestTrait;
    use UnchangeableAddressOwnerTestTrait;

    private const ENTITY_CLASS                  = LeadAddress::class;
    private const ENTITY_TYPE                   = 'leadaddresses';
    private const OWNER_ENTITY_TYPE             = 'leads';
    private const OWNER_RELATIONSHIP            = 'owner';
    private const CREATE_MIN_REQUEST_DATA       = 'create_lead_address_min.yml';
    private const OWNER_CREATE_MIN_REQUEST_DATA = 'create_lead_min.yml';
    private const IS_REGION_REQUIRED            = false;
    private const COUNTRY_REGION_ADDRESS_REF    = 'lead_address1';
    private const PRIMARY_ADDRESS_REF           = 'lead_address1';
    private const UNCHANGEABLE_ADDRESS_REF      = 'lead_address1';
    private const OWNER_REF                     = 'lead1';
    private const ANOTHER_OWNER_REF             = 'another_lead';
    private const ANOTHER_OWNER_ADDRESS_2_REF   = 'another_lead_address2';

    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures(['@OroSalesBundle/Tests/Functional/Api/DataFixtures/lead_addresses.yml']);
    }

    /**
     * @param LeadAddress $address
     *
     * @return Lead
     */
    private function getOwner(LeadAddress $address)
    {
        return $address->getOwner();
    }

    public function testGetList()
    {
        $response = $this->cget(
            ['entity' => self::ENTITY_TYPE]
        );

        $this->assertResponseContains('cget_lead_address.yml', $response);
    }

    public function testGetListFilterByCountry()
    {
        $response = $this->cget(
            ['entity' => self::ENTITY_TYPE],
            ['filter' => ['country' => '<toString(@country_israel->iso2Code)>']]
        );

        $this->assertResponseContains('cget_lead_lead_address_filter_country.yml', $response);
    }

    public function testGetListFilterByRegion()
    {
        $response = $this->cget(
            ['entity' => self::ENTITY_TYPE],
            ['filter' => ['region' => '<toString(@region_israel_telaviv->combinedCode)>']]
        );

        $this->assertResponseContains('cget_lead_address_filter_region.yml', $response);
    }

    public function testGet()
    {
        $response = $this->get(
            ['entity' => self::ENTITY_TYPE, 'id' => '<toString(@lead_address2->id)>']
        );

        $this->assertResponseContains('get_lead_address.yml', $response);
    }

    public function testCreate()
    {
        $leadId = $this->getReference('lead1')->getId();
        $countryId = $this->getReference('country_usa')->getIso2Code();
        $regionId = $this->getReference('region_usa_california')->getCombinedCode();

        $response = $this->post(
            ['entity' => self::ENTITY_TYPE],
            'create_lead_address.yml'
        );

        $addressId = (int)$this->getResourceId($response);
        $responseContent = $this->updateResponseContent('create_lead_address.yml', $response);
        $this->assertResponseContains($responseContent, $response);

        /** @var LeadAddress $address */
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
        self::assertEquals($leadId, $address->getOwner()->getId());
        self::assertEquals($countryId, $address->getCountry()->getIso2Code());
        self::assertEquals($regionId, $address->getRegion()->getCombinedCode());
    }

    public function testCreateWithRequiredDataOnly()
    {
        $leadId = $this->getReference('lead1')->getId();
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

        /** @var LeadAddress $address */
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
        self::assertEquals($leadId, $address->getOwner()->getId());
        self::assertEquals($countryId, $address->getCountry()->getIso2Code());
        self::assertTrue(null === $address->getRegion());
    }

    public function testUpdate()
    {
        $addressId = $this->getReference('lead_address2')->getId();
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

        /** @var LeadAddress $address */
        $address = $this->getEntityManager()
            ->find(self::ENTITY_CLASS, $addressId);
        self::assertNotNull($address);
        self::assertEquals('Updated Address', $address->getLabel());
    }

    public function testDelete()
    {
        $addressId = $this->getReference('lead_address2')->getId();

        $this->delete(
            ['entity' => self::ENTITY_TYPE, 'id' => (string)$addressId]
        );

        $address = $this->getEntityManager()
            ->find(self::ENTITY_CLASS, $addressId);
        self::assertTrue(null === $address);
    }

    public function testDeleteList()
    {
        $addressId = $this->getReference('lead_address2')->getId();

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
        $addressId = $this->getReference('lead_address1')->getId();
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
        /** @var LeadAddress $address */
        $address = $this->getReference('lead_address1');
        $addressId = $address->getId();
        $ownerId = $address->getOwner()->getId();
        $data = [
            'data' => [
                'type'          => self::ENTITY_TYPE,
                'id'            => (string)$addressId,
                'relationships' => [
                    'owner' => [
                        'data' => [
                            'type' => 'leads',
                            'id'   => '<toString(@another_lead->id)>'
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
        $addressId = $this->getReference('lead_address1')->getId();
        $data = [
            'data' => [
                'type' => 'leads',
                'id'   => '<toString(@lead1->id)>'
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
}
