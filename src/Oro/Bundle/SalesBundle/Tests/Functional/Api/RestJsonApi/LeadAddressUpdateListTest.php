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
    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures([
            LoadCountriesAndRegions::class,
            '@OroSalesBundle/Tests/Functional/Api/DataFixtures/leads_for_address_update_list.yml'
        ]);
    }

    private function getLeadAddressId(string $street): int
    {
        /** @var LeadAddress|null $address */
        $address = $this->getEntityManager()->getRepository(LeadAddress::class)
            ->findOneBy(['street' => $street]);
        if (null === $address) {
            throw new \RuntimeException(sprintf('The address "%s" not found.', $street));
        }

        return $address->getId();
    }

    private function getLeadAddressIndex(array $data, string $street): int
    {
        foreach ($data as $i => $item) {
            if ($item['attributes']['street'] === $street) {
                return $i;
            }
        }
        throw new \RuntimeException(sprintf('The address "%s" not found.', $street));
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
        $address1Id = $this->getLeadAddressId('15a Lewis Circle');
        $address2Id = $this->getLeadAddressId('16 Lewis Circle');
        $responseContent = [
            'data'     => [
                'type'          => 'leads',
                'id'            => '<toString(@lead1->id)>',
                'relationships' => [
                    'addresses' => [
                        'data' => [
                            ['type' => 'leadaddresses', 'id' => (string)$address1Id],
                            ['type' => 'leadaddresses', 'id' => (string)$address2Id]
                        ]
                    ]
                ]
            ],
            'included' => $data['data']
        ];
        $address1Index = $this->getLeadAddressIndex($responseContent['included'], '15a Lewis Circle');
        $address2Index = $this->getLeadAddressIndex($responseContent['included'], '16 Lewis Circle');
        $responseContent['included'][$address1Index]['id'] = (string)$address1Id;
        $responseContent['included'][$address1Index]['attributes']['primary'] = true;
        $responseContent['included'][$address2Index]['id'] = (string)$address2Id;
        $responseContent['included'][$address2Index]['attributes']['primary'] = false;
        $this->assertResponseContains($responseContent, $response);
    }
}
