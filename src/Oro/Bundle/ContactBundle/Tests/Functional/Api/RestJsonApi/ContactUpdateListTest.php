<?php

namespace Oro\Bundle\ContactBundle\Tests\Functional\Api\RestJsonApi;

use Oro\Bundle\ApiBundle\Tests\Functional\RestJsonApiUpdateListTestCase;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Tests\Functional\Api\DataFixtures\LoadContactsData;

/**
 * @dbIsolationPerTest
 */
class ContactUpdateListTest extends RestJsonApiUpdateListTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures([LoadContactsData::class]);
    }

    public function testCreateEntities()
    {
        $this->processUpdateList(
            Contact::class,
            [
                'data' => [
                    [
                        'type'       => 'contacts',
                        'attributes' => ['firstName' => 'New Contact 1']
                    ],
                    [
                        'type'       => 'contacts',
                        'attributes' => ['firstName' => 'New Contact 2']
                    ]
                ]
            ]
        );

        $response = $this->cget(['entity' => 'contacts']);
        $responseContent = $this->updateResponseContent(
            [
                'data' => [
                    [
                        'type'       => 'contacts',
                        'id'         => '<toString(@contact1->id)>',
                        'attributes' => ['firstName' => 'Contact 1']
                    ],
                    [
                        'type'       => 'contacts',
                        'id'         => '<toString(@contact2->id)>',
                        'attributes' => ['firstName' => 'Contact 2']
                    ],
                    [
                        'type'       => 'contacts',
                        'id'         => 'new',
                        'attributes' => ['firstName' => 'New Contact 1']
                    ],
                    [
                        'type'       => 'contacts',
                        'id'         => 'new',
                        'attributes' => ['firstName' => 'New Contact 2']
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
            Contact::class,
            [
                'data' => [
                    [
                        'meta'       => ['update' => true],
                        'type'       => 'contacts',
                        'id'         => '<toString(@contact1->id)>',
                        'attributes' => ['firstName' => 'Updated Contact 1']
                    ],
                    [
                        'meta'       => ['update' => true],
                        'type'       => 'contacts',
                        'id'         => '<toString(@contact2->id)>',
                        'attributes' => ['firstName' => 'Updated Contact 2']
                    ]
                ]
            ]
        );

        $response = $this->cget(['entity' => 'contacts']);
        $this->assertResponseContains(
            [
                'data' => [
                    [
                        'type'       => 'contacts',
                        'id'         => '<toString(@contact1->id)>',
                        'attributes' => ['firstName' => 'Updated Contact 1']
                    ],
                    [
                        'type'       => 'contacts',
                        'id'         => '<toString(@contact2->id)>',
                        'attributes' => ['firstName' => 'Updated Contact 2']
                    ]
                ]
            ],
            $response
        );
    }

    public function testCreateAndUpdateEntities()
    {
        $this->processUpdateList(
            Contact::class,
            [
                'data' => [
                    [
                        'type'       => 'contacts',
                        'attributes' => ['firstName' => 'New Contact 1']
                    ],
                    [
                        'meta'       => ['update' => true],
                        'type'       => 'contacts',
                        'id'         => '<toString(@contact1->id)>',
                        'attributes' => ['firstName' => 'Updated Contact 1']
                    ]
                ]
            ]
        );

        $response = $this->cget(['entity' => 'contacts']);
        $responseContent = $this->updateResponseContent(
            [
                'data' => [
                    [
                        'type'       => 'contacts',
                        'id'         => '<toString(@contact1->id)>',
                        'attributes' => ['firstName' => 'Updated Contact 1']
                    ],
                    [
                        'type'       => 'contacts',
                        'id'         => '<toString(@contact2->id)>',
                        'attributes' => ['firstName' => 'Contact 2']
                    ],
                    [
                        'type'       => 'contacts',
                        'id'         => 'new',
                        'attributes' => ['firstName' => 'New Contact 1']
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
            Contact::class,
            [
                'data'     => [
                    [
                        'type'          => 'contacts',
                        'attributes'    => ['firstName' => 'New Contact 1'],
                        'relationships' => ['accounts' => ['data' => [['type' => 'accounts', 'id' => 'a1']]]]
                    ],
                    [
                        'type'          => 'contacts',
                        'attributes'    => ['firstName' => 'New Contact 2'],
                        'relationships' => ['reportsTo' => ['data' => ['type' => 'contacts', 'id' => 'c1']]]
                    ]
                ],
                'included' => [
                    ['type' => 'accounts', 'id' => 'a1', 'attributes' => ['name' => 'New Account 1']],
                    ['type' => 'contacts', 'id' => 'c1', 'attributes' => ['firstName' => 'Included Contact 1']]
                ]
            ]
        );

        $response = $this->cget(
            ['entity' => 'contacts'],
            [
                'filter[id][gt]'   => '@contact2->id',
                'include'          => 'accounts',
                'fields[accounts]' => 'name',
                'fields[contacts]' => 'firstName,accounts,reportsTo',
                'sort'             => 'firstName'
            ]
        );
        $responseContent = $this->updateResponseContent(
            [
                'data'     => [
                    [
                        'type'          => 'contacts',
                        'id'            => 'new',
                        'attributes'    => ['firstName' => 'Included Contact 1']
                    ],
                    [
                        'type'          => 'contacts',
                        'id'            => 'new',
                        'attributes'    => ['firstName' => 'New Contact 1'],
                        'relationships' => ['accounts' => ['data' => [['type' => 'accounts', 'id' => 'new']]]]
                    ],
                    [
                        'type'          => 'contacts',
                        'id'            => 'new',
                        'attributes'    => ['firstName' => 'New Contact 2'],
                        'relationships' => ['reportsTo' => ['data' => ['type' => 'contacts', 'id' => 'new']]]
                    ]
                ],
                'included' => [
                    ['type' => 'accounts', 'id' => 'new', 'attributes' => ['name' => 'New Account 1']]
                ],
            ],
            $response
        );
        $this->assertResponseContains($responseContent, $response);
    }

    public function testTryToCreateEntitiesWithErrorsInIncludes()
    {
        $operationId = $this->processUpdateList(
            Contact::class,
            [
                'data'     => [
                    [
                        'type'          => 'contacts',
                        'attributes'    => ['firstName' => 'New Contact 2'],
                        'relationships' => ['reportsTo' => ['data' => ['type' => 'contacts', 'id' => 'c1']]]
                    ]
                ],
                'included' => [
                    [
                        'type'          => 'contacts',
                        'id'            => 'c1',
                        'attributes'    => ['firstName' => 'Included Contact 1'],
                        'relationships' => ['reportsTo' => ['data' => ['type' => 'contacts', 'id' => 'c2']]]
                    ],
                    [
                        'type'          => 'contacts',
                        'id'            => 'c2',
                        'relationships' => ['reportsTo' => ['data' => ['type' => 'contacts', 'id' => 'c2']]]
                    ]
                ]
            ],
            false
        );

        $this->assertAsyncOperationErrors(
            [
                [
                    'id'     => $operationId . '-1-1',
                    'status' => 400,
                    'title'  => 'has contact information constraint',
                    'detail' => 'At least one of the fields First name, Last name, Emails or Phones must be defined.',
                    'source' => ['pointer' => '/included/1'],
                ],
            ],
            $operationId
        );
    }
}
