<?php

namespace Oro\Bundle\SalesBundle\Tests\Functional\Api\RestJsonApi;

use Oro\Bundle\ApiBundle\Tests\Functional\RestJsonApiUpdateListTestCase;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\SalesBundle\Tests\Functional\Api\DataFixtures\LoadLeadsData;

/**
 * @dbIsolationPerTest
 */
class LeadUpdateListTest extends RestJsonApiUpdateListTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures([LoadLeadsData::class]);
    }

    public function testCreateEntities()
    {
        $this->processUpdateList(
            Lead::class,
            [
                'data' => [
                    [
                        'type'       => 'leads',
                        'attributes' => ['name' => 'New Lead 1']
                    ],
                    [
                        'type'       => 'leads',
                        'attributes' => ['name' => 'New Lead 2']
                    ]
                ]
            ]
        );

        $response = $this->cget(['entity' => 'leads'], ['fields[leads]' => 'name']);
        $responseContent = $this->updateResponseContent(
            [
                'data' => [
                    [
                        'type'       => 'leads',
                        'id'         => '<toString(@lead1->id)>',
                        'attributes' => ['name' => 'Lead 1']
                    ],
                    [
                        'type'       => 'leads',
                        'id'         => '<toString(@lead2->id)>',
                        'attributes' => ['name' => 'Lead 2']
                    ],
                    [
                        'type'       => 'leads',
                        'id'         => 'new',
                        'attributes' => ['name' => 'New Lead 1']
                    ],
                    [
                        'type'       => 'leads',
                        'id'         => 'new',
                        'attributes' => ['name' => 'New Lead 2']
                    ]
                ]
            ],
            $response
        );
        $this->assertResponseContains($responseContent, $response);
    }

    public function testUpdateEntities()
    {
        $this->processUpdateList(
            Lead::class,
            [
                'data' => [
                    [
                        'meta'       => ['update' => true],
                        'type'       => 'leads',
                        'id'         => '<toString(@lead1->id)>',
                        'attributes' => ['name' => 'Updated Lead 1']
                    ],
                    [
                        'meta'       => ['update' => true],
                        'type'       => 'leads',
                        'id'         => '<toString(@lead2->id)>',
                        'attributes' => ['name' => 'Updated Lead 2']
                    ]
                ]
            ]
        );

        $response = $this->cget(['entity' => 'leads'], ['fields[leads]' => 'name']);
        $this->assertResponseContains(
            [
                'data' => [
                    [
                        'type'       => 'leads',
                        'id'         => '<toString(@lead1->id)>',
                        'attributes' => ['name' => 'Updated Lead 1']
                    ],
                    [
                        'type'       => 'leads',
                        'id'         => '<toString(@lead2->id)>',
                        'attributes' => ['name' => 'Updated Lead 2']
                    ]
                ]
            ],
            $response
        );
    }

    public function testCreateAndUpdateEntities()
    {
        $this->processUpdateList(
            Lead::class,
            [
                'data' => [
                    [
                        'type'       => 'leads',
                        'attributes' => ['name' => 'New Lead 1']
                    ],
                    [
                        'meta'       => ['update' => true],
                        'type'       => 'leads',
                        'id'         => '<toString(@lead1->id)>',
                        'attributes' => ['name' => 'Updated Lead 1']
                    ]
                ]
            ]
        );

        $response = $this->cget(['entity' => 'leads'], ['fields[leads]' => 'name']);
        $responseContent = $this->updateResponseContent(
            [
                'data' => [
                    [
                        'type'       => 'leads',
                        'id'         => '<toString(@lead1->id)>',
                        'attributes' => ['name' => 'Updated Lead 1']
                    ],
                    [
                        'type'       => 'leads',
                        'id'         => '<toString(@lead2->id)>',
                        'attributes' => ['name' => 'Lead 2']
                    ],
                    [
                        'type'       => 'leads',
                        'id'         => 'new',
                        'attributes' => ['name' => 'New Lead 1']
                    ]
                ]
            ],
            $response
        );
        $this->assertResponseContains($responseContent, $response);
    }

    public function testCreateEntitiesWithIncludes()
    {
        $this->processUpdateList(
            Lead::class,
            [
                'data'     => [
                    [
                        'type'          => 'leads',
                        'attributes'    => ['name' => 'New Lead 1'],
                        'relationships' => [
                            'opportunities' => ['data' => [['type' => 'opportunities', 'id' => 'opp1']]]
                        ]
                    ],
                    [
                        'type'          => 'leads',
                        'attributes'    => ['name' => 'New Lead 2'],
                        'relationships' => [
                            'contact' => ['data' => ['type' => 'contacts', 'id' => 'c1']]
                        ]
                    ],
                ],
                'included' => [
                    [
                        'type'       => 'contacts',
                        'id'         => 'c1',
                        'attributes' => ['firstName' => 'Included contact 1'],
                    ],
                    [
                        'type'          => 'opportunities',
                        'id'            => 'opp1',
                        'attributes'    => ['name' => 'New Opportunity'],
                        'relationships' => [
                            'account' => ['data' => ['type' => 'accounts', 'id' => 'acc1']],
                            'status'  => ['data' => ['type' => 'opportunitystatuses', 'id' => 'lost']],
                        ],
                    ],
                    [
                        'type'       => 'accounts',
                        'id'         => 'acc1',
                        'attributes' => ['name' => 'New Account 1'],
                    ],
                ],
            ]
        );

        $response = $this->cget(
            ['entity' => 'leads'],
            [
                'filter[id][gt]' => '@lead2->id',
                'fields[leads]'  => 'name,opportunities,contact'
            ]
        );
        $responseContent = $this->updateResponseContent(
            [
                'data'     => [
                    [
                        'type'          => 'leads',
                        'id'            => 'new',
                        'attributes'    => ['name' => 'New Lead 1'],
                        'relationships' => [
                            'opportunities' => ['data' => [['type' => 'opportunities', 'id' => 'new']]]
                        ]
                    ],
                    [
                        'type'          => 'leads',
                        'id'            => 'new',
                        'attributes'    => ['name' => 'New Lead 2'],
                        'relationships' => ['contact' => ['data' => ['type' => 'contacts', 'id' => 'new']]]
                    ]
                ]
            ],
            $response
        );
        $this->assertResponseContains($responseContent, $response);

        /** @var Lead $lead1 */
        $lead1 = $this->getEntityManager()->getRepository(Lead::class)->findOneBy(['name' => 'New Lead 1']);
        /** @var Opportunity $leadOpportunity */
        $leadOpportunity = $lead1->getOpportunities()->first();
        self::assertEquals('New Opportunity', $leadOpportunity->getName());
        self::assertEquals('New Account 1', $leadOpportunity->getCustomerAssociation()->getAccount()->getName());
        self::assertEquals('lost', $leadOpportunity->getStatus()->getId());

        /** @var Lead $lead1 */
        $lead2 = $this->getEntityManager()->getRepository(Lead::class)->findOneBy(['name' => 'New Lead 2']);
        self::assertEquals('Included contact 1', $lead2->getContact()->getFirstName());
    }

    public function testTryToCreateEntitiesWithErrorsInIncludes()
    {
        $operationId = $this->processUpdateList(
            Lead::class,
            [
                'data'     => [
                    [
                        'type'          => 'leads',
                        'attributes'    => ['name' => 'New Lead 1'],
                        'relationships' => [
                            'opportunities' => ['data' => [['type' => 'opportunities', 'id' => 'opp1']]]
                        ]
                    ]
                ],
                'included' => [
                    ['type' => 'opportunities', 'id' => 'opp1', 'attributes' => ['name' => 'New Opportunity']]
                ]
            ],
            false
        );

        $this->assertAsyncOperationErrors(
            [
                [
                    'id'     => $operationId . '-1-1',
                    'status' => 400,
                    'title'  => 'form constraint',
                    'detail' => 'Either an account or a customer should be set.',
                    'source' => ['pointer' => '/included/0']
                ],
                [
                    'id'     => $operationId . '-1-2',
                    'status' => 400,
                    'title'  => 'not blank constraint',
                    'detail' => 'This value should not be blank.',
                    'source' => ['pointer' => '/included/0/relationships/status/data']
                ]
            ],
            $operationId
        );
    }
}
