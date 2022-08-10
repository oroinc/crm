<?php

namespace Oro\Bundle\ContactBundle\Tests\Functional\Api\RestJsonApi;

use Oro\Bundle\ApiBundle\Tests\Functional\RestJsonApiTestCase;
use Oro\Bundle\ContactBundle\Entity\Group;

/**
 * @dbIsolationPerTest
 */
class ContactGroupTest extends RestJsonApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures([
            '@OroContactBundle/Tests/Functional/Api/DataFixtures/contact_groups.yml'
        ]);
    }

    public function testGetList(): void
    {
        $response = $this->cget(
            ['entity' => 'contactgroups'],
            ['filter[label]' => 'Group 2']
        );
        $this->assertResponseContains(
            [
                'data' => [
                    [
                        'type'          => 'contactgroups',
                        'id'            => '<toString(@contact_group2->id)>',
                        'attributes'    => [
                            'label' => 'Group 2'
                        ],
                        'relationships' => [
                            'owner'        => [
                                'data' => ['type' => 'users', 'id' => '<toString(@user->id)>']
                            ],
                            'organization' => [
                                'data' => ['type' => 'organizations', 'id' => '<toString(@organization->id)>']
                            ]
                        ]
                    ]
                ]
            ],
            $response
        );
    }

    public function testGet(): void
    {
        $response = $this->get(['entity' => 'contactgroups', 'id' => '<toString(@contact_group1->id)>']);
        $this->assertResponseContains(
            [
                'data' => [
                    'type'          => 'contactgroups',
                    'id'            => '<toString(@contact_group1->id)>',
                    'attributes'    => [
                        'label' => 'Group 1'
                    ],
                    'relationships' => [
                        'owner'        => [
                            'data' => ['type' => 'users', 'id' => '<toString(@user->id)>']
                        ],
                        'organization' => [
                            'data' => ['type' => 'organizations', 'id' => '<toString(@organization->id)>']
                        ]
                    ]
                ]
            ],
            $response
        );
    }

    public function testDelete(): void
    {
        $groupId = $this->getReference('contact_group1')->getId();

        $this->delete(['entity' => 'contactgroups', 'id' => (string)$groupId]);

        $deletedGroup = $this->getEntityManager()
            ->find(Group::class, $groupId);
        self::assertTrue(null === $deletedGroup);
    }

    public function testDeleteList(): void
    {
        $groupId = $this->getReference('contact_group1')->getId();

        $this->cdelete(
            ['entity' => 'contactgroups'],
            ['filter[label]' => 'Group 1']
        );

        $deletedGroup = $this->getEntityManager()
            ->find(Group::class, $groupId);
        self::assertTrue(null === $deletedGroup);
    }

    public function testCreate(): void
    {
        $organizationId = $this->getReference('organization')->getId();
        $userId = $this->getReference('user')->getId();

        $data = [
            'data' => [
                'type'       => 'contactgroups',
                'attributes' => [
                    'label' => 'New Group'
                ]
            ]
        ];
        $response = $this->post(['entity' => 'contactgroups'], $data);

        $expectedData = $data;
        $expectedData['data']['relationships']['organization']['data'] = [
            'type' => 'organizations',
            'id'   => (string)$organizationId
        ];
        $expectedData['data']['relationships']['owner']['data'] = [
            'type' => 'users',
            'id'   => (string)$userId
        ];
        $this->assertResponseContains($expectedData, $response);

        $contactGroup = $this->getEntityManager()
            ->find(Group::class, $this->getResourceId($response));
        self::assertNotNull($contactGroup);
        self::assertEquals('New Group', $contactGroup->getLabel());
        self::assertEquals($organizationId, $contactGroup->getOrganization()->getId());
        self::assertEquals($userId, $contactGroup->getOwner()->getId());
    }

    public function testUpdate(): void
    {
        $groupId = $this->getReference('contact_group1')->getId();

        $response = $this->patch(
            ['entity' => 'contactgroups', 'id' => (string)$groupId],
            [
                'data' => [
                    'type'       => 'contactgroups',
                    'id'         => (string)$groupId,
                    'attributes' => [
                        'label' => 'Updated Group'
                    ]
                ]
            ]
        );

        $contactGroup = $this->getEntityManager()
            ->find(Group::class, $this->getResourceId($response));
        self::assertNotNull($contactGroup);
        self::assertEquals('Updated Group', $contactGroup->getLabel());
    }

    public function testTryToCreateGroupWithoutLabel(): void
    {
        $response = $this->post(
            ['entity' => 'contactgroups'],
            ['data' => ['type' => 'contactgroups']],
            [],
            false
        );
        $this->assertResponseValidationError(
            [
                'title'  => 'not blank constraint',
                'detail' => 'This value should not be blank.',
                'source' => ['pointer' => '/data/attributes/label']
            ],
            $response
        );
    }

    public function testTryToSetGroupLabelToNull(): void
    {
        $groupId = $this->getReference('contact_group1')->getId();

        $response = $this->patch(
            ['entity' => 'contactgroups', 'id' => (string)$groupId],
            ['data' => ['type' => 'contactgroups', 'id' => (string)$groupId, 'attributes' => ['label' => null]]],
            [],
            false
        );
        $this->assertResponseValidationError(
            [
                'title'  => 'not blank constraint',
                'detail' => 'This value should not be blank.',
                'source' => ['pointer' => '/data/attributes/label']
            ],
            $response
        );
    }

    public function testTryToSetGroupTooSmallLabel(): void
    {
        $groupId = $this->getReference('contact_group1')->getId();

        $response = $this->patch(
            ['entity' => 'contactgroups', 'id' => (string)$groupId],
            ['data' => ['type' => 'contactgroups', 'id' => (string)$groupId, 'attributes' => ['label' => 'g']]],
            [],
            false
        );
        $this->assertResponseValidationError(
            [
                'title'  => 'length constraint',
                'detail' => 'This value is too short. It should have 3 characters or more.',
                'source' => ['pointer' => '/data/attributes/label']
            ],
            $response
        );
    }
}
