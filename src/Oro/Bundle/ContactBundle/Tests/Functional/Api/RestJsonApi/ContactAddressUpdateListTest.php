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

    private function getContactAddressId(string $street): int
    {
        /** @var ContactAddress|null $address */
        $address = $this->getEntityManager()->getRepository(ContactAddress::class)
            ->findOneBy(['street' => $street]);
        if (null === $address) {
            throw new \RuntimeException(sprintf('The address "%s" not found.', $street));
        }

        return $address->getId();
    }

    private function getContactAddressIndex(array $data, string $street): int
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
        $address1Id = $this->getContactAddressId('15a Lewis Circle');
        $address2Id = $this->getContactAddressId('16 Lewis Circle');
        $responseContent = [
            'data'     => [
                'type'          => 'contacts',
                'id'            => '<toString(@contact1->id)>',
                'relationships' => [
                    'addresses' => [
                        'data' => [
                            ['type' => 'contactaddresses', 'id' => (string)$address1Id],
                            ['type' => 'contactaddresses', 'id' => (string)$address2Id]
                        ]
                    ]
                ]
            ],
            'included' => $data['data']
        ];
        $address1Index = $this->getContactAddressIndex($responseContent['included'], '15a Lewis Circle');
        $address2Index = $this->getContactAddressIndex($responseContent['included'], '16 Lewis Circle');
        $responseContent['included'][$address1Index]['id'] = (string)$address1Id;
        $responseContent['included'][$address1Index]['attributes']['primary'] = true;
        $responseContent['included'][$address2Index]['id'] = (string)$address2Id;
        $responseContent['included'][$address2Index]['attributes']['primary'] = false;
        $this->assertResponseContains($responseContent, $response);
    }
}
