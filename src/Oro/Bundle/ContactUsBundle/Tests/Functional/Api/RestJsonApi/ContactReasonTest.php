<?php

namespace Oro\Bundle\ContactUsBundle\Tests\Functional\Api\RestJsonApi;

use Oro\Bundle\ApiBundle\Tests\Functional\RestJsonApiTestCase;
use Oro\Bundle\ContactUsBundle\Entity\ContactReason;
use Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue;

/**
 * @dbIsolationPerTest
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class ContactReasonTest extends RestJsonApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures([
            '@OroContactUsBundle/Tests/Functional/Api/DataFixtures/contact_reasons.yml'
        ]);
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
    }

    public function testGetList(): void
    {
        $response = $this->cget(
            ['entity' => 'contactreasons'],
            ['filter[id][gte]' => '<toString(@reason2->id)>', 'include' => 'labels']
        );
        $this->assertResponseContains(
            [
                'data'     => [
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
                ],
                'included' => [
                    [
                        'type'       => 'localizedfallbackvalues',
                        'id'         => '<toString(@reason2_title1->id)>',
                        'attributes' => ['string' => 'Reason 2']
                    ],
                    [
                        'type'       => 'localizedfallbackvalues',
                        'id'         => '<toString(@reason3_title1->id)>',
                        'attributes' => ['string' => 'Reason 3']
                    ]
                ]
            ],
            $response
        );
    }

    public function testGet(): void
    {
        $response = $this->get(
            ['entity' => 'contactreasons', 'id' => '<toString(@reason1->id)>'],
            ['include' => 'labels']
        );
        $this->assertResponseContains(
            [
                'data'     => [
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
                ],
                'included' => [
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

    public function testDelete(): void
    {
        $reasonId = $this->getReference('reason1')->getId();

        $this->delete(['entity' => 'contactreasons', 'id' => (string)$reasonId]);

        $deletedReason = $this->getEntityManager()
            ->find(ContactReason::class, $reasonId);
        self::assertNotNull($deletedReason);
        self::assertNotNull($deletedReason->getDeletedAt());
    }

    public function testDeleteList(): void
    {
        $reasonId = $this->getReference('reason1')->getId();

        $this->cdelete(
            ['entity' => 'contactreasons'],
            ['filter[id]' => '<toString(@reason1->id)>']
        );

        $deletedReason = $this->getEntityManager()
            ->find(ContactReason::class, $reasonId);
        self::assertNotNull($deletedReason);
        self::assertNotNull($deletedReason->getDeletedAt());
    }

    public function testCreate(): void
    {
        $this->enableTwig();

        $data = [
            'data'     => [
                'type'          => 'contactreasons',
                'relationships' => [
                    'labels' => [
                        'data' => [
                            ['type' => 'localizedfallbackvalues', 'id' => 'new_label']
                        ]
                    ]
                ]
            ],
            'included' => [
                [
                    'type'       => 'localizedfallbackvalues',
                    'id'         => 'new_label',
                    'attributes' => [
                        'string' => 'New Reason'
                    ]
                ]
            ]
        ];
        $response = $this->post(['entity' => 'contactreasons'], $data);

        $contactReason = $this->getEntityManager()
            ->find(ContactReason::class, $this->getResourceId($response));
        self::assertNotNull($contactReason);
        self::assertTrue(count($contactReason->getTitles()) > 0);

        $expectedData = $data;
        $expectedData['data']['relationships']['labels']['data'] = [];
        foreach ($contactReason->getTitles() as $title) {
            $expectedData['data']['relationships']['labels']['data'][] = [
                'type' => 'localizedfallbackvalues',
                'id'   => (string)$title->getId()
            ];
        }
        unset($expectedData['included']);

        $this->assertResponseContains($expectedData, $response);
    }

    public function testTryToUpdateDeactivatedAt(): void
    {
        $reasonId = $this->getReference('reason1')->getId();

        $data = [
            'data' => [
                'type'       => 'contactreasons',
                'id'         => '<toString(@reason1->id)>',
                'attributes' => [
                    'deactivatedAt' => '2022-01-01T10:20:30Z'
                ]
            ]
        ];
        $response = $this->patch(
            ['entity' => 'contactreasons', 'id' => (string)$reasonId],
            $data
        );

        $expectedData = $data;
        $expectedData['data']['attributes']['deactivatedAt'] = null;
        $this->assertResponseContains($expectedData, $response);

        $contactReason = $this->getEntityManager()
            ->find(ContactReason::class, $reasonId);
        self::assertNull($contactReason->getDeletedAt());
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

    public function testAddRelationshipForLabels(): void
    {
        $reasonId = $this->getReference('reason1')->getId();

        $this->postRelationship(
            ['entity' => 'contactreasons', 'id' => (string)$reasonId, 'association' => 'labels'],
            [
                'data' => [
                    ['type' => 'localizedfallbackvalues', 'id' => '<toString(@localized_fallback_value1->id)>']
                ]
            ]
        );

        $contactReason = $this->getEntityManager()
            ->find(ContactReason::class, $reasonId);
        self::assertCount(2, $contactReason->getTitles());
    }

    public function testUpdateRelationshipForLabels(): void
    {
        $reasonId = $this->getReference('reason1')->getId();

        $this->postRelationship(
            ['entity' => 'contactreasons', 'id' => (string)$reasonId, 'association' => 'labels'],
            [
                'data' => [
                    ['type' => 'localizedfallbackvalues', 'id' => '<toString(@reason1_title1->id)>'],
                    ['type' => 'localizedfallbackvalues', 'id' => '<toString(@localized_fallback_value1->id)>']
                ]
            ]
        );

        $contactReason = $this->getEntityManager()
            ->find(ContactReason::class, $reasonId);
        self::assertCount(2, $contactReason->getTitles());
    }

    public function testDeleteRelationshipForLabels(): void
    {
        $reasonId = $this->getReference('reason1')->getId();

        $this->deleteRelationship(
            ['entity' => 'contactreasons', 'id' => (string)$reasonId, 'association' => 'labels'],
            [
                'data' => [
                    ['type' => 'localizedfallbackvalues', 'id' => '<toString(@reason1_title1->id)>']
                ]
            ]
        );

        $contactReason = $this->getEntityManager()
            ->find(ContactReason::class, $reasonId);
        self::assertCount(0, $contactReason->getTitles());
    }
}
