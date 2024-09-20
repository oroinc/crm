<?php

namespace Oro\Bundle\SalesBundle\Tests\Functional\Api\RestJsonApi;

use Oro\Bundle\ApiBundle\Tests\Functional\RestJsonApiTestCase;

class OpportunityStatusTest extends RestJsonApiTestCase
{
    public function testGetList(): void
    {
        $response = $this->cget(['entity' => 'opportunitystatuses']);
        $this->assertResponseContains(
            [
                'data' => [
                    [
                        'type'       => 'opportunitystatuses',
                        'id'         => 'identification_alignment',
                        'attributes' => [
                            'name'     => 'Identification & Alignment',
                            'priority' => 2,
                            'default'  => false
                        ]
                    ],
                    [
                        'type'       => 'opportunitystatuses',
                        'id'         => 'in_progress',
                        'attributes' => [
                            'name'     => 'Open',
                            'priority' => 1,
                            'default'  => true
                        ]
                    ],
                    [
                        'type'       => 'opportunitystatuses',
                        'id'         => 'lost',
                        'attributes' => [
                            'name'     => 'Closed Lost',
                            'priority' => 7,
                            'default'  => false
                        ]
                    ],
                    [
                        'type'       => 'opportunitystatuses',
                        'id'         => 'needs_analysis',
                        'attributes' => [
                            'name'     => 'Needs Analysis',
                            'priority' => 3,
                            'default'  => false
                        ]
                    ],
                    [
                        'type'       => 'opportunitystatuses',
                        'id'         => 'negotiation',
                        'attributes' => [
                            'name'     => 'Negotiation',
                            'priority' => 5,
                            'default'  => false
                        ]
                    ],
                    [
                        'type'       => 'opportunitystatuses',
                        'id'         => 'solution_development',
                        'attributes' => [
                            'name'     => 'Solution Development',
                            'priority' => 4,
                            'default'  => false
                        ]
                    ],
                    [
                        'type'       => 'opportunitystatuses',
                        'id'         => 'won',
                        'attributes' => [
                            'name'     => 'Closed Won',
                            'priority' => 6,
                            'default'  => false
                        ]
                    ]
                ]
            ],
            $response
        );
    }

    public function testGetListSortedByPriority(): void
    {
        $response = $this->cget(['entity' => 'opportunitystatuses'], ['sort' => 'priority']);
        $this->assertResponseContains(
            [
                'data' => [
                    [
                        'type'       => 'opportunitystatuses',
                        'id'         => 'in_progress',
                        'attributes' => [
                            'name'     => 'Open',
                            'priority' => 1,
                            'default'  => true
                        ]
                    ],
                    [
                        'type'       => 'opportunitystatuses',
                        'id'         => 'identification_alignment',
                        'attributes' => [
                            'name'     => 'Identification & Alignment',
                            'priority' => 2,
                            'default'  => false
                        ]
                    ],
                    [
                        'type'       => 'opportunitystatuses',
                        'id'         => 'needs_analysis',
                        'attributes' => [
                            'name'     => 'Needs Analysis',
                            'priority' => 3,
                            'default'  => false
                        ]
                    ],
                    [
                        'type'       => 'opportunitystatuses',
                        'id'         => 'solution_development',
                        'attributes' => [
                            'name'     => 'Solution Development',
                            'priority' => 4,
                            'default'  => false
                        ]
                    ],
                    [
                        'type'       => 'opportunitystatuses',
                        'id'         => 'negotiation',
                        'attributes' => [
                            'name'     => 'Negotiation',
                            'priority' => 5,
                            'default'  => false
                        ]
                    ],
                    [
                        'type'       => 'opportunitystatuses',
                        'id'         => 'won',
                        'attributes' => [
                            'name'     => 'Closed Won',
                            'priority' => 6,
                            'default'  => false
                        ]
                    ],
                    [
                        'type'       => 'opportunitystatuses',
                        'id'         => 'lost',
                        'attributes' => [
                            'name'     => 'Closed Lost',
                            'priority' => 7,
                            'default'  => false
                        ]
                    ]
                ]
            ],
            $response
        );
    }

    public function testTryToGetListWithTitles(): void
    {
        $response = $this->cget(['entity' => 'opportunitystatuses'], ['meta' => 'title'], [], false);
        $this->assertResponseValidationError(
            [
                'title'  => 'filter constraint',
                'detail' => 'The filter is not supported.',
                'source' => ['parameter' => 'meta']
            ],
            $response
        );
    }

    public function testGet(): void
    {
        $response = $this->get(['entity' => 'opportunitystatuses', 'id' => 'in_progress']);
        $this->assertResponseContains(
            [
                'data' => [
                    'type'       => 'opportunitystatuses',
                    'id'         => 'in_progress',
                    'attributes' => [
                        'name'     => 'Open',
                        'priority' => 1,
                        'default'  => true
                    ]
                ]
            ],
            $response
        );
    }

    public function testTryToCreate(): void
    {
        $response = $this->post(
            ['entity' => 'opportunitystatuses', 'id' => 'new_status'],
            ['data' => ['type' => 'opportunitystatuses', 'id' => 'new_status']],
            [],
            false
        );
        self::assertMethodNotAllowedResponse($response, 'OPTIONS, GET');
    }

    public function testTryToDelete(): void
    {
        $response = $this->delete(
            ['entity' => 'opportunitystatuses', 'id' => 'in_progress'],
            [],
            [],
            false
        );
        self::assertMethodNotAllowedResponse($response, 'OPTIONS, GET');
    }

    public function testTryToDeleteList(): void
    {
        $response = $this->cdelete(
            ['entity' => 'opportunitystatuses'],
            ['filter[id]' => 'in_progress'],
            [],
            false
        );
        self::assertMethodNotAllowedResponse($response, 'OPTIONS, GET');
    }

    public function testGetOptionsForList(): void
    {
        $response = $this->options(
            $this->getListRouteName(),
            ['entity' => 'opportunitystatuses']
        );
        self::assertAllowResponseHeader($response, 'OPTIONS, GET');
    }

    public function testOptionsForItem(): void
    {
        $response = $this->options(
            $this->getItemRouteName(),
            ['entity' => 'opportunitystatuses', 'id' => 'in_progress']
        );
        self::assertAllowResponseHeader($response, 'OPTIONS, GET');
    }
}
