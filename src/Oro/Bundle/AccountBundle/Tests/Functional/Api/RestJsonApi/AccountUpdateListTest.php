<?php

namespace Oro\Bundle\AccountBundle\Tests\Functional\Api\RestJsonApi;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ApiBundle\Tests\Functional\RestJsonApiUpdateListTestCase;

/**
 * @dbIsolationPerTest
 */
class AccountUpdateListTest extends RestJsonApiUpdateListTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures(['@OroAccountBundle/Tests/Functional/DataFixtures/accounts_data.yml']);
    }

    public function testCreateEntities()
    {
        $this->processUpdateList(
            Account::class,
            [
                'data' => [
                    [
                        'type'       => 'accounts',
                        'attributes' => ['name' => 'New Account 1']
                    ],
                    [
                        'type'       => 'accounts',
                        'attributes' => ['name' => 'New Account 2']
                    ]
                ]
            ]
        );

        $response = $this->cget(['entity' => 'accounts'], ['filter[id][gte]' => '@account1->id']);
        $responseContent = $this->updateResponseContent(
            [
                'data' => [
                    [
                        'type'       => 'accounts',
                        'id'         => '<toString(@account1->id)>',
                        'attributes' => ['name' => 'Account 1']
                    ],
                    [
                        'type'       => 'accounts',
                        'id'         => '<toString(@account2->id)>',
                        'attributes' => ['name' => 'Account 2']
                    ],
                    [
                        'type'       => 'accounts',
                        'id'         => 'new',
                        'attributes' => ['name' => 'New Account 1']
                    ],
                    [
                        'type'       => 'accounts',
                        'id'         => 'new',
                        'attributes' => ['name' => 'New Account 2']
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
            Account::class,
            [
                'data' => [
                    [
                        'meta'       => ['update' => true],
                        'type'       => 'accounts',
                        'id'         => '<toString(@account1->id)>',
                        'attributes' => ['name' => 'Updated Account 1']
                    ],
                    [
                        'meta'       => ['update' => true],
                        'type'       => 'accounts',
                        'id'         => '<toString(@account2->id)>',
                        'attributes' => ['name' => 'Updated Account 2']
                    ]
                ]
            ]
        );

        $response = $this->cget(['entity' => 'accounts'], ['filter[id][gte]' => '@account1->id']);
        $this->assertResponseContains(
            [
                'data' => [
                    [
                        'type'       => 'accounts',
                        'id'         => '<toString(@account1->id)>',
                        'attributes' => ['name' => 'Updated Account 1']
                    ],
                    [
                        'type'       => 'accounts',
                        'id'         => '<toString(@account2->id)>',
                        'attributes' => ['name' => 'Updated Account 2']
                    ]
                ]
            ],
            $response
        );
    }

    public function testCreateAndUpdateEntities()
    {
        $this->processUpdateList(
            Account::class,
            [
                'data' => [
                    [
                        'type'       => 'accounts',
                        'attributes' => ['name' => 'New Account 1']
                    ],
                    [
                        'meta'       => ['update' => true],
                        'type'       => 'accounts',
                        'id'         => '<toString(@account1->id)>',
                        'attributes' => ['name' => 'Updated Account 1']
                    ]
                ]
            ]
        );

        $response = $this->cget(['entity' => 'accounts'], ['filter[id][gte]' => '@account1->id']);
        $responseContent = $this->updateResponseContent(
            [
                'data' => [
                    [
                        'type'       => 'accounts',
                        'id'         => '<toString(@account1->id)>',
                        'attributes' => ['name' => 'Updated Account 1']
                    ],
                    [
                        'type'       => 'accounts',
                        'id'         => '<toString(@account2->id)>',
                        'attributes' => ['name' => 'Account 2']
                    ],
                    [
                        'type'       => 'accounts',
                        'id'         => 'new',
                        'attributes' => ['name' => 'New Account 1']
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
            Account::class,
            [
                'data'     => [
                    [
                        'type'          => 'accounts',
                        'attributes'    => ['name' => 'New Account 1'],
                        'relationships' => [
                            'contacts'       => [
                                'data' => [['type' => 'contacts', 'id' => 'c1'], ['type' => 'contacts', 'id' => 'c2']]
                            ],
                            'defaultContact' => ['data' => ['type' => 'contacts', 'id' => 'c1']]
                        ]
                    ],
                    [
                        'type'          => 'accounts',
                        'attributes'    => ['name' => 'New Account 2'],
                        'relationships' => [
                            'contacts'       => [
                                'data' => [['type' => 'contacts', 'id' => 'c1'], ['type' => 'contacts', 'id' => 'c2']]
                            ],
                            'defaultContact' => ['data' => ['type' => 'contacts', 'id' => 'c2']]
                        ]
                    ]
                ],
                'included' => [
                    ['type' => 'contacts', 'id' => 'c1', 'attributes' => ['firstName' => 'Contact1']],
                    ['type' => 'contacts', 'id' => 'c2', 'attributes' => ['firstName' => 'Contact2']]
                ]
            ]
        );

        $response = $this->cget(
            ['entity' => 'accounts'],
            [
                'filter[id][gt]'   => '@account2->id',
                'fields[accounts]' => 'name,contacts,defaultContact'
            ]
        );
        $responseContent = $this->updateResponseContent(
            [
                'data' => [
                    [
                        'type'          => 'accounts',
                        'id'            => 'new',
                        'attributes'    => ['name' => 'New Account 1'],
                        'relationships' => [
                            'contacts'       => [
                                'data' => [
                                    ['type' => 'contacts', 'id' => 'new'],
                                    ['type' => 'contacts', 'id' => 'new']
                                ]
                            ],
                            'defaultContact' => ['data' => ['type' => 'contacts', 'id' => 'new']]
                        ]
                    ],
                    [
                        'type'          => 'accounts',
                        'id'            => 'new',
                        'attributes'    => ['name' => 'New Account 2'],
                        'relationships' => [
                            'contacts'       => [
                                'data' => [
                                    ['type' => 'contacts', 'id' => 'new'],
                                    ['type' => 'contacts', 'id' => 'new']]
                            ],
                            'defaultContact' => ['data' => ['type' => 'contacts', 'id' => 'new']]
                        ]
                    ]
                ]
            ],
            $response
        );

        /** @var Account $account1 */
        $account1 = $this->getEntityManager()->getRepository(Account::class)->findOneBy(['name' => 'New Account 1']);
        self::assertEquals('Contact1', $account1->getDefaultContact()->getFirstName());

        /** @var Account $account2 */
        $account2 = $this->getEntityManager()->getRepository(Account::class)->findOneBy(['name' => 'New Account 2']);
        self::assertEquals('Contact2', $account2->getDefaultContact()->getFirstName());

        $this->assertResponseContains($responseContent, $response);
    }

    public function testTryToCreateEntitiesWithErrorsInIncludes()
    {
        $operationId = $this->processUpdateList(
            Account::class,
            [
                'data'     => [
                    [
                        'type'          => 'accounts',
                        'attributes'    => ['name' => 'New Account 1'],
                        'relationships' => [
                            'contacts'       => [
                                'data' => [['type' => 'contacts', 'id' => 'c1'], ['type' => 'contacts', 'id' => 'c2']],
                            ],
                            'defaultContact' => ['data' => ['type' => 'contacts', 'id' => 'c1']]
                        ]
                    ]
                ],
                'included' => [
                    [
                        'type'       => 'contacts',
                        'id'         => 'c1',
                        'attributes' => ['firstName' => 'Contact1', 'primaryEmail' => 'test']
                    ],
                    [
                        'type'       => 'contacts',
                        'id'         => 'c2',
                        'attributes' => ['fax' => '878-90-06']
                    ],
                ],
            ],
            false
        );

        $this->assertAsyncOperationErrors(
            [
                [
                    'id'     => $operationId . '-1-1',
                    'status' => 400,
                    'title'  => 'email constraint',
                    'detail' => 'This value is not a valid email address.',
                    'source' => ['pointer' => '/included/0/attributes/primaryEmail']
                ],
                [
                    'id'     => $operationId . '-1-2',
                    'status' => 400,
                    'title'  => 'has contact information constraint',
                    'detail' => 'At least one of the fields First name, Last name, Emails or Phones must be defined.',
                    'source' => ['pointer' => '/included/1']
                ]
            ],
            $operationId
        );
    }
}
