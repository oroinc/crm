<?php

namespace Oro\Bundle\ContactBundle\Tests\Functional\Api\RestJsonApi;

use Oro\Bundle\AddressBundle\Tests\Functional\DataFixtures\LoadAddressTypes;
use Oro\Bundle\AddressBundle\Tests\Functional\DataFixtures\LoadCountriesAndRegions;
use Oro\Bundle\ApiBundle\Tests\Functional\RestJsonApiUpdateListTestCase;
use Oro\Bundle\ContactBundle\Entity\ContactAddress;

/**
 * @dbIsolationPerTest
 */
class ContactAddressUpdateListTest extends RestJsonApiUpdateListTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures([
            LoadCountriesAndRegions::class,
            LoadAddressTypes::class,
            '@OroContactBundle/Tests/Functional/Api/DataFixtures/contacts_for_address_update_list.yml'
        ]);
    }

    public function testCreateEntities(): void
    {
        $data = [
            'data' => [
                [
                    'type'          => 'contactaddresses',
                    'attributes'    => [
                        'street'       => '15a Lewis Circle',
                        'city'         => 'Wilmington',
                        'postalCode'   => '90002',
                        'organization' => 'SOI-73 STR5 Account 2'
                    ],
                    'relationships' => [
                        'types'   => [
                            'data' => [
                                ['type' => 'addresstypes', 'id' => '<toString(@billing->name)>']
                            ]
                        ],
                        'country' => [
                            'data' => ['type' => 'countries', 'id' => 'US']
                        ],
                        'region'  => [
                            'data' => ['type' => 'regions', 'id' => 'US-AL']
                        ],
                        'owner'   => [
                            'data' => ['type' => 'contacts', 'id' => '<toString(@contact1->id)>']
                        ]
                    ]
                ],
                [
                    'type'          => 'contactaddresses',
                    'attributes'    => [
                        'street'       => '16 Lewis Circle',
                        'city'         => 'Wilmington',
                        'postalCode'   => '90002',
                        'organization' => 'SOI-73 STR5 Account 2'
                    ],
                    'relationships' => [
                        'types'   => [
                            'data' => [
                                ['type' => 'addresstypes', 'id' => '<toString(@shipping->name)>']
                            ]
                        ],
                        'country' => [
                            'data' => ['type' => 'countries', 'id' => 'US']
                        ],
                        'region'  => [
                            'data' => ['type' => 'regions', 'id' => 'US-AL']
                        ],
                        'owner'   => [
                            'data' => ['type' => 'contacts', 'id' => '<toString(@contact1->id)>']
                        ]
                    ]
                ]
            ]
        ];
        $this->processUpdateList(ContactAddress::class, $data);

        $response = $this->get(
            ['entity' => 'contacts', 'id' => '<toString(@contact1->id)>'],
            ['include' => 'addresses']
        );
        $responseContent = [
            'data'     => [
                'type'          => 'contacts',
                'id'            => '<toString(@contact1->id)>',
                'relationships' => [
                    'addresses' => [
                        'data' => [
                            ['type' => 'contactaddresses', 'id' => 'new'],
                            ['type' => 'contactaddresses', 'id' => 'new']
                        ]
                    ]
                ]
            ],
            'included' => $data['data']
        ];
        $responseContent['included'][0]['id'] = 'new';
        $responseContent['included'][0]['attributes']['primary'] = true;
        $responseContent['included'][1]['id'] = 'new';
        $responseContent['included'][1]['attributes']['primary'] = false;
        $responseContent = $this->updateResponseContent($responseContent, $response);
        $this->assertResponseContains($responseContent, $response);
    }
}
