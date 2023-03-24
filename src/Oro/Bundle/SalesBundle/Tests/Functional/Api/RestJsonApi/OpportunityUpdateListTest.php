<?php

namespace Oro\Bundle\SalesBundle\Tests\Functional\Api\RestJsonApi;

use Oro\Bundle\ApiBundle\Tests\Functional\RestJsonApiUpdateListTestCase;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\SalesBundle\Tests\Functional\Api\DataFixtures\LoadOpportunitiesData;

/**
 * @dbIsolationPerTest
 */
class OpportunityUpdateListTest extends RestJsonApiUpdateListTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures([LoadOpportunitiesData::class]);
    }

    public function testCreateEntities()
    {
        $this->processUpdateList(
            Opportunity::class,
            [
                'data'     => [
                    [
                        'type'          => 'opportunities',
                        'attributes'    => ['name' => 'New Opportunity1'],
                        'relationships' => [
                            'account' => ['data' => ['type' => 'accounts', 'id' => 'acc1']],
                            'status'  => ['data' => ['type' => 'opportunitystatuses', 'id' => 'lost']]
                        ]
                    ],
                    [
                        'type'          => 'opportunities',
                        'attributes'    => ['name' => 'New Opportunity2'],
                        'relationships' => [
                            'account' => ['data' => ['type' => 'accounts', 'id' => 'acc2']],
                            'status'  => ['data' => ['type' => 'opportunitystatuses', 'id' => 'won']]
                        ]
                    ]
                ],
                'included' => [
                    ['type' => 'accounts', 'id' => 'acc1', 'attributes' => ['name' => 'New Account 1']],
                    ['type' => 'accounts', 'id' => 'acc2', 'attributes' => ['name' => 'New Account 2']]
                ]
            ]
        );

        $response = $this->cget(
            ['entity' => 'opportunities'],
            ['fields[opportunities]' => 'name,account,status']
        );
        $responseContent = $this->updateResponseContent(
            [
                'data'     => [
                    [
                        'type'          => 'opportunities',
                        'id'            => '<toString(@opportunity1->id)>',
                        'attributes'    => ['name' => 'Opportunity 1'],
                        'relationships' => [
                            'status'  => ['data' => ['type' => 'opportunitystatuses', 'id' => 'lost']],
                            'account' => ['data' => ['type' => 'accounts', 'id' => '<toString(@account1->id)>']]
                        ]
                    ],
                    [
                        'type'          => 'opportunities',
                        'id'            => '<toString(@opportunity2->id)>',
                        'attributes'    => ['name' => 'Opportunity 2'],
                        'relationships' => [
                            'status'  => ['data' => ['type' => 'opportunitystatuses', 'id' => 'won']],
                            'account' => ['data' => ['type' => 'accounts', 'id' => '<toString(@account1->id)>']]
                        ]
                    ],
                    [
                        'type'          => 'opportunities',
                        'id'            => 'new',
                        'attributes'    => ['name' => 'New Opportunity1'],
                        'relationships' => [
                            'status'  => ['data' => ['type' => 'opportunitystatuses', 'id' => 'lost']],
                            'account' => ['data' => ['type' => 'accounts', 'id' => 'new']]
                        ]
                    ],
                    [
                        'type'          => 'opportunities',
                        'id'            => 'new',
                        'attributes'    => ['name' => 'New Opportunity2'],
                        'relationships' => [
                            'status'  => ['data' => ['type' => 'opportunitystatuses', 'id' => 'won']],
                            'account' => ['data' => ['type' => 'accounts', 'id' => 'new']]
                        ]
                    ]
                ]
            ],
            $response
        );
        $this->assertResponseContains($responseContent, $response);

        $repo = $this->getEntityManager()->getRepository(Opportunity::class);
        $opportunity1 = $repo->findOneBy(['name' => 'New Opportunity1']);
        self::assertEquals('New Account 1', $opportunity1->getCustomerAssociation()->getAccount()->getName());
        self::assertEquals('lost', $opportunity1->getStatus()->getId());

        $opportunity2 = $repo->findOneBy(['name' => 'New Opportunity2']);
        self::assertEquals('New Account 2', $opportunity2->getCustomerAssociation()->getAccount()->getName());
        self::assertEquals('won', $opportunity2->getStatus()->getId());
    }

    public function testUpdateEntities()
    {
        $this->processUpdateList(
            Opportunity::class,
            [
                'data'     => [
                    [
                        'meta'       => ['update' => true],
                        'type'       => 'opportunities',
                        'id'         => '<toString(@opportunity1->id)>',
                        'attributes' => ['name' => 'Updated Opportunity 1']
                    ],
                    [
                        'meta'          => ['update' => true],
                        'type'          => 'opportunities',
                        'id'            => '<toString(@opportunity2->id)>',
                        'relationships' => [
                            'account' => ['data' => ['type' => 'accounts', 'id' => 'new_acc']],
                        ]
                    ]
                ],
                'included' => [
                    ['type' => 'accounts', 'id' => 'new_acc', 'attributes' => ['name' => 'New Account 1']]
                ],
            ]
        );

        $response = $this->cget(['entity' => 'opportunities'], ['fields[opportunities]' => 'name,account']);
        $responseContent = $this->updateResponseContent(
            [
                'data' => [
                    [
                        'type'          => 'opportunities',
                        'id'            => '<toString(@opportunity1->id)>',
                        'attributes'    => ['name' => 'Updated Opportunity 1'],
                        'relationships' => [
                            'account' => ['data' => ['type' => 'accounts', 'id' => '<toString(@account1->id)>']]
                        ]
                    ],
                    [
                        'type'          => 'opportunities',
                        'id'            => '<toString(@opportunity2->id)>',
                        'attributes'    => ['name' => 'Opportunity 2'],
                        'relationships' => [
                            'account' => ['data' => ['type' => 'accounts', 'id' => 'new']]
                        ]
                    ]
                ]
            ],
            $response
        );
        $this->assertResponseContains($responseContent, $response);
    }

    public function testCreateAndUpdateEntities()
    {
        $this->processUpdateList(
            Opportunity::class,
            [
                'data'     => [
                    [
                        'type'          => 'opportunities',
                        'attributes'    => ['name' => 'New Opportunity1'],
                        'relationships' => [
                            'account' => ['data' => ['type' => 'accounts', 'id' => 'acc1']],
                            'status'  => ['data' => ['type' => 'opportunitystatuses', 'id' => 'lost']]
                        ]
                    ],
                    [
                        'meta'       => ['update' => true],
                        'type'       => 'opportunities',
                        'id'         => '<toString(@opportunity1->id)>',
                        'attributes' => ['name' => 'Updated Opportunity 1']
                    ]
                ],
                'included' => [
                    ['type' => 'accounts', 'id' => 'acc1', 'attributes' => ['name' => 'New Account 1']]
                ]
            ]
        );

        $response = $this->cget(['entity' => 'opportunities'], ['fields[opportunities]' => 'name,account,status']);
        $responseContent = $this->updateResponseContent(
            [
                'data' => [
                    [
                        'type'          => 'opportunities',
                        'id'            => '<toString(@opportunity1->id)>',
                        'attributes'    => ['name' => 'Updated Opportunity 1'],
                        'relationships' => [
                            'status'  => ['data' => ['type' => 'opportunitystatuses', 'id' => 'lost']],
                            'account' => ['data' => ['type' => 'accounts', 'id' => '<toString(@account1->id)>']]
                        ]
                    ],
                    [
                        'type'          => 'opportunities',
                        'id'            => '<toString(@opportunity2->id)>',
                        'attributes'    => ['name' => 'Opportunity 2'],
                        'relationships' => [
                            'status'  => ['data' => ['type' => 'opportunitystatuses', 'id' => 'won']],
                            'account' => ['data' => ['type' => 'accounts', 'id' => '<toString(@account1->id)>']]
                        ]
                    ],
                    [
                        'type'          => 'opportunities',
                        'id'            => 'new',
                        'attributes'    => ['name' => 'New Opportunity1'],
                        'relationships' => [
                            'status'  => ['data' => ['type' => 'opportunitystatuses', 'id' => 'lost']],
                            'account' => ['data' => ['type' => 'accounts', 'id' => 'new']]
                        ]
                    ]
                ]
            ],
            $response
        );
        $this->assertResponseContains($responseContent, $response);
    }

    public function testTryToCreateEntitiesWithErrorsInIncludes()
    {
        $operationId = $this->processUpdateList(
            Opportunity::class,
            [
                'data'     => [
                    [
                        'type'          => 'opportunities',
                        'attributes'    => ['name' => 'New Opportunity1'],
                        'relationships' => [
                            'account' => ['data' => ['type' => 'accounts', 'id' => 'acc1']],
                            'status'  => ['data' => ['type' => 'opportunitystatuses', 'id' => 'lost']],
                        ]
                    ]
                ],
                'included' => [['type' => 'accounts', 'id' => 'acc1', 'attributes' => []]]
            ],
            false
        );

        $this->assertAsyncOperationErrors(
            [
                [
                    'id'     => $operationId.'-1-1',
                    'status' => 400,
                    'title'  => 'request data constraint',
                    'detail' => 'The \'attributes\' property should not be empty',
                    'source' => ['pointer' => '/included/0/attributes']
                ]
            ],
            $operationId
        );
    }
}
