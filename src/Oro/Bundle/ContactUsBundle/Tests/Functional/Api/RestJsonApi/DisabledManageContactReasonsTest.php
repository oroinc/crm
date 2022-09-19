<?php

namespace Oro\Bundle\ContactUsBundle\Tests\Functional\Api\RestJsonApi;

use Oro\Bundle\ApiBundle\Tests\Functional\RestJsonApiTestCase;
use Oro\Bundle\ContactUsBundle\Entity\ContactReason;
use Oro\Bundle\FeatureToggleBundle\Tests\Functional\Stub\FeatureCheckerStub;
use Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue;
use Symfony\Component\HttpFoundation\Response;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class DisabledManageContactReasonsTest extends RestJsonApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures([
            '@OroContactUsBundle/Tests/Functional/Api/DataFixtures/contact_reasons.yml'
        ]);
        $this->client->disableReboot();
        for ($i = 1; $i <= 3; $i++) {
            /** @var ContactReason $reason */
            $reason = $this->getReference('reason' . $i);
            $j = 1;
            /** @var LocalizedFallbackValue $title */
            foreach ($reason->getTitles() as $title) {
                $this->getReferenceRepository()->setReference('reason' . $i . '_title' . $j, $title);
                $j++;
            }
        }

        /** @var FeatureCheckerStub $featureChecker */
        $featureChecker = self::getContainer()->get('oro_featuretoggle.checker.feature_checker');
        $featureChecker->setFeatureEnabled('manage_contact_reasons', false);
    }

    public function testGetList(): void
    {
        $response = $this->cget(
            ['entity' => 'contactreasons'],
            ['filter[id][gte]' => '<toString(@reason2->id)>', 'include' => 'labels']
        );
        $this->assertResponseContains(
            [
                'data' => [
                    [
                        'type'          => 'contactreasons',
                        'id'            => '<toString(@reason2->id)>',
                        'attributes'    => [
                            'deactivatedAt' => null
                        ],
                        'relationships' => [
                            'labels' => [
                                'data' => [
                                    ['type' => 'localizedfallbackvalues', 'id' => '<toString(@reason2_title1->id)>']
                                ]
                            ]
                        ]
                    ],
                    [
                        'type'          => 'contactreasons',
                        'id'            => '<toString(@reason3->id)>',
                        'attributes'    => [
                            'deactivatedAt' => '2022-01-01T10:20:30Z'
                        ],
                        'relationships' => [
                            'labels' => [
                                'data' => [
                                    ['type' => 'localizedfallbackvalues', 'id' => '<toString(@reason3_title1->id)>']
                                ]
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
        $response = $this->get(
            ['entity' => 'contactreasons', 'id' => '<toString(@reason1->id)>']
        );
        $this->assertResponseContains(
            [
                'data' => [
                    'type'          => 'contactreasons',
                    'id'            => '<toString(@reason1->id)>',
                    'attributes'    => [
                        'deactivatedAt' => null
                    ],
                    'relationships' => [
                        'labels' => [
                            'data' => [
                                ['type' => 'localizedfallbackvalues', 'id' => '<toString(@reason1_title1->id)>']
                            ]
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
            ['entity' => 'contactreasons', 'id' => '<toString(@reason1->id)>'],
            [],
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_NOT_FOUND);
    }

    public function testTryToDeleteList(): void
    {
        $response = $this->cdelete(
            ['entity' => 'contactreasons'],
            ['filter[id]' => '<toString(@reason1->id)>'],
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_NOT_FOUND);
    }

    public function testTryToCreate(): void
    {
        $response = $this->post(
            ['entity' => 'contactreasons'],
            ['data' => ['type' => 'contactreasons']],
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_NOT_FOUND);
    }

    public function testTryToUpdate(): void
    {
        $reasonId = $this->getReference('reason1')->getId();

        $response = $this->patch(
            ['entity' => 'contactreasons', 'id' => (string)$reasonId],
            ['data' => ['type' => 'contactreasons', 'id' => (string)$reasonId]],
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_NOT_FOUND);
    }

    public function testGetSubresourceForLabels(): void
    {
        $response = $this->getSubresource(
            ['entity' => 'contactreasons', 'id' => '<toString(@reason1->id)>', 'association' => 'labels']
        );
        $this->assertResponseContains(
            [
                'data' => [
                    [
                        'type'       => 'localizedfallbackvalues',
                        'id'         => '<toString(@reason1_title1->id)>',
                        'attributes' => ['string' => 'Reason 1']
                    ]
                ]
            ],
            $response
        );
    }

    public function testGetRelationshipForLabels(): void
    {
        $response = $this->getSubresource(
            ['entity' => 'contactreasons', 'id' => '<toString(@reason1->id)>', 'association' => 'labels']
        );
        $this->assertResponseContains(
            [
                'data' => [
                    ['type' => 'localizedfallbackvalues', 'id' => '<toString(@reason1_title1->id)>']
                ]
            ],
            $response
        );
    }

    public function testTryToAddRelationshipForLabels(): void
    {
        $response = $this->postRelationship(
            ['entity' => 'contactreasons', 'id' => '<toString(@reason1->id)>', 'association' => 'labels'],
            [
                'data' => [
                    ['type' => 'localizedfallbackvalues', 'id' => '<toString(@localized_fallback_value1->id)>']
                ]
            ],
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_NOT_FOUND);
    }

    public function testTryToUpdateRelationshipForLabels(): void
    {
        $response = $this->postRelationship(
            ['entity' => 'contactreasons', 'id' => '<toString(@reason1->id)>', 'association' => 'labels'],
            [
                'data' => [
                    ['type' => 'localizedfallbackvalues', 'id' => '<toString(@reason1_title1->id)>'],
                    ['type' => 'localizedfallbackvalues', 'id' => '<toString(@localized_fallback_value1->id)>']
                ]
            ],
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_NOT_FOUND);
    }

    public function testTryToDeleteRelationshipForLabels(): void
    {
        $response = $this->deleteRelationship(
            ['entity' => 'contactreasons', 'id' => '<toString(@reason1->id)>', 'association' => 'labels'],
            [
                'data' => [
                    ['type' => 'localizedfallbackvalues', 'id' => '<toString(@reason1_title1->id)>']
                ]
            ],
            [],
            false
        );
        self::assertResponseStatusCodeEquals($response, Response::HTTP_NOT_FOUND);
    }
}
