<?php

namespace Oro\Bundle\ContactBundle\Tests\Functional\Api\RestJsonApi;

use Oro\Bundle\ApiBundle\Tests\Functional\RestJsonApiTestCase;
use Oro\Bundle\FeatureToggleBundle\Tests\Functional\Stub\FeatureCheckerStub;
use Symfony\Component\HttpFoundation\Response;

class DisabledManageContactGroupsTest extends RestJsonApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures([
            '@OroContactBundle/Tests/Functional/Api/DataFixtures/contact_groups.yml'
        ]);
        $this->client->disableReboot();

        /** @var FeatureCheckerStub $featureChecker */
        $featureChecker = self::getContainer()->get('oro_featuretoggle.checker.feature_checker');
        $featureChecker->setFeatureEnabled('manage_contact_groups', false);
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

    public function testTryToDelete(): void
    {
        $response = $this->delete(
            ['entity' => 'contactgroups', 'id' => '<toString(@contact_group1->id)>'],
            [],
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_NOT_FOUND);
    }

    public function testTryToDeleteList(): void
    {
        $response = $this->cdelete(
            ['entity' => 'contactgroups'],
            ['filter[label]' => 'Group 1'],
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_NOT_FOUND);
    }

    public function testTryToCreate(): void
    {
        $response = $this->post(
            ['entity' => 'contactgroups'],
            ['data' => ['type' => 'contactgroups', 'attributes' => ['label' => 'New Group']]],
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_NOT_FOUND);
    }

    public function testTryToUpdate(): void
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
            ],
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_NOT_FOUND);
    }
}
