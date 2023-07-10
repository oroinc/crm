<?php

namespace Oro\Bundle\SalesBundle\Tests\Functional\Api\RestJsonApi;

use Oro\Bundle\AddressBundle\Tests\Functional\DataFixtures\LoadCountriesAndRegions;
use Oro\Bundle\ApiBundle\Tests\Functional\RestJsonApiUpdateListTestCase;
use Oro\Bundle\SalesBundle\Entity\LeadAddress;

/**
 * @dbIsolationPerTest
 */
class LeadAddressUpdateListTest extends RestJsonApiUpdateListTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures([
            LoadCountriesAndRegions::class,
            '@OroSalesBundle/Tests/Functional/Api/DataFixtures/leads_for_address_update_list.yml'
        ]);
    }

    public function testCreateEntities(): void
    {
        $data = [
            'data' => [
                [
                    'type'          => 'leadaddresses',
                    'attributes'    => [
                        'street'       => '15a Lewis Circle',
                        'city'         => 'Wilmington',
                        'postalCode'   => '90002',
                        'organization' => 'SOI-73 STR5 Account 2'
                    ],
                    'relationships' => [
                        'country' => [
                            'data' => ['type' => 'countries', 'id' => 'US']
                        ],
                        'region'  => [
                            'data' => ['type' => 'regions', 'id' => 'US-AL']
                        ],
                        'owner'   => [
                            'data' => ['type' => 'leads', 'id' => '<toString(@lead1->id)>']
                        ]
                    ]
                ],
                [
                    'type'          => 'leadaddresses',
                    'attributes'    => [
                        'street'       => '16 Lewis Circle',
                        'city'         => 'Wilmington',
                        'postalCode'   => '90002',
                        'organization' => 'SOI-73 STR5 Account 2'
                    ],
                    'relationships' => [
                        'country' => [
                            'data' => ['type' => 'countries', 'id' => 'US']
                        ],
                        'region'  => [
                            'data' => ['type' => 'regions', 'id' => 'US-AL']
                        ],
                        'owner'   => [
                            'data' => ['type' => 'leads', 'id' => '<toString(@lead1->id)>']
                        ]
                    ]
                ]
            ]
        ];
        $this->processUpdateList(LeadAddress::class, $data);

        $response = $this->get(
            ['entity' => 'leads', 'id' => '<toString(@lead1->id)>'],
            ['include' => 'addresses']
        );
        $responseContent = [
            'data'     => [
                'type'          => 'leads',
                'id'            => '<toString(@lead1->id)>',
                'relationships' => [
                    'addresses' => [
                        'data' => [
                            ['type' => 'leadaddresses', 'id' => 'new'],
                            ['type' => 'leadaddresses', 'id' => 'new']
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
