<?php

namespace Oro\Bundle\SalesBundle\Tests\Functional\Widget;

use Oro\Bundle\DashboardBundle\Entity\Widget;
use Oro\Bundle\FilterBundle\Form\Type\Filter\AbstractDateFilterType;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\SalesBundle\Tests\Functional\Fixture\LoadOpportunityStatisticsWidgetFixture;

class OpportunityStatisticsTest extends BaseStatistics
{
    public array $metrics = [
        'new_opportunities_count'          => 'New Opportunities count',
        'new_opportunities_amount'         => 'New Opportunities amount',
        'won_opportunities_to_date_count'  => 'Won Opportunities to date count',
        'won_opportunities_to_date_amount' => 'Won Opportunities to date amount'
    ];

    protected function setUp(): void
    {
        $this->initClient([], $this->generateBasicAuthHeader());
        $this->loadFixtures([LoadOpportunityStatisticsWidgetFixture::class]);
    }

    public function testDefaultConfiguration(): void
    {
        $this->getConfigureDialog();

        $crawler = $this->client->getCrawler();
        $form = $crawler->selectButton('Save')->form();

        $data = $form->getPhpValues();

        $this->createMetricsElements($data, 'opportunity_statistics');
        $this->setComparePrevious($data, 'opportunity_statistics', true);
        $this->createAndSetDateRangeFormElements($data, 'opportunity_statistics', [
            'type' => AbstractDateFilterType::TYPE_ALL_TIME
        ]);

        $this->client->request($form->getMethod(), $form->getUri(), $data);

        $response = $this->client->getResponse();
        self::assertEquals(200, $response->getStatusCode(), 'Failed in submit widget configuration options !');

        $crawler = $this->client->request(
            'GET',
            $this->getUrl(
                'oro_dashboard_itemized_data_widget',
                [
                    'widget' => 'opportunity_statistics',
                    'bundle' => 'OroDashboard',
                    'name' => 'bigNumbers',
                    '_widgetId' => $this->getWidget()->getId()
                ]
            )
        );

        $response = $this->client->getResponse();
        self::assertEquals(200, $response->getStatusCode(), 'Failed in getting widget view !');
        self::assertNotEmpty($crawler->html());

        $newOpportunityCountMetric = $crawler->filterXPath(
            $this->getMetricValueByLabel(
                $this->metrics['new_opportunities_count']
            )
        );
        self::assertEquals(
            2,
            $newOpportunityCountMetric->text(),
            '"New Opportunities Count" metric does not much expected value'
        );

        $newOpportunityAmountMetric = $crawler->filterXPath(
            $this->getMetricValueByLabel(
                $this->metrics['new_opportunities_amount']
            )
        );
        self::assertEquals(
            '$60,000.00',
            $newOpportunityAmountMetric->getNode(0)->nodeValue,
            '"New Opportunities Amount" metric does not much expected value'
        );

        $newOpportunityCountMetric = $crawler->filterXPath(
            $this->getMetricValueByLabel(
                $this->metrics['won_opportunities_to_date_count']
            )
        );
        self::assertEquals(
            1,
            $newOpportunityCountMetric->text(),
            '"Won Opportunities Count" metric does not much expected value'
        );

        $newOpportunityCountMetric = $crawler->filterXPath(
            $this->getMetricValueByLabel(
                $this->metrics['won_opportunities_to_date_amount']
            )
        );
        self::assertEquals(
            '$10,000.00',
            $newOpportunityCountMetric->text(),
            '"Won Opportunities Count" metric does not much expected value'
        );
    }

    /**
     * @dataProvider widgetProvider
     */
    public function testCustomConfiguration(
        string $owners,
        array $dateRange,
        bool $comparePrevious,
        array $advancedFilters,
        array $result,
        array $previousResult
    ): void {
        $this->getConfigureDialog();

        $crawler = $this->client->getCrawler();
        $form = $crawler->selectButton('Save')->form();

        $data = $form->getPhpValues();
        $data['opportunity_statistics']['owners']['users'] = $owners;

        $this->createMetricsElements($data, 'opportunity_statistics');
        $this->setComparePrevious($data, 'opportunity_statistics', $comparePrevious);
        $this->setAdvancedFilters($data, 'opportunity_statistics', $advancedFilters);
        $this->createAndSetDateRangeFormElements($data, 'opportunity_statistics', $dateRange);

        $this->client->request($form->getMethod(), $form->getUri(), $data);

        $this->inspectResult($result, $previousResult);
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function widgetProvider(): array
    {
        return [
            'Apply owner filter with date range filter on today' => [
                'owners' => '1',
                'dateRange' => ['type' => AbstractDateFilterType::TYPE_TODAY],
                'comparePrevious' => true,
                'advancedFilters' => [
                    'entity' => Opportunity::class,
                    'filters' => [
                        'columnName' => 'updatedAt',
                        'criterion' => [
                            'filter' => 'datetime',
                            'data' => [
                                'type' => AbstractDateFilterType::TYPE_MORE_THAN,
                                'part' => 'value',
                                'value' => [
                                    'start' => '2016-01-01 00:00',
                                    'end' => ''
                                ]
                            ]
                        ]
                    ]
                ],
                'widgetItemResult' => [
                    'new_opportunities_count' => 0,
                    'new_opportunities_amount' => '$0.00',
                    'won_opportunities_to_date_count' => 0,
                    'won_opportunities_to_date_amount' => '$0.00',
                ],
                'widgetItemPreviousResult' => [
                    'new_opportunities_count' => 'No changes',
                    'new_opportunities_amount' => 'No changes',
                    'won_opportunities_to_date_count' => 'No changes',
                    'won_opportunities_to_date_amount' => 'No changes'
                ]
            ],
            'Apply "All time" date range filter' => [
                'owners' => '',
                'dateRange' => ['type' => AbstractDateFilterType::TYPE_ALL_TIME],
                'comparePrevious' => true,
                'advancedFilters' => [],
                'widgetItemResult' => [
                    'new_opportunities_count' => 2,
                    'new_opportunities_amount' => '$60,000.00',
                    'won_opportunities_to_date_count' => 1,
                    'won_opportunities_to_date_amount' => '$10,000.00',
                ],
                'widgetItemPreviousResult' => []
            ],
            'Apply "Today" date range filter' => [
                'owners' => '',
                'dateRange' => ['type' => AbstractDateFilterType::TYPE_TODAY],
                'comparePrevious' => true,
                'advancedFilters' => [],
                'widgetItemResult' => [
                    'new_opportunities_count' => 2,
                    'new_opportunities_amount' => '$60,000.00',
                    'won_opportunities_to_date_count' => 1,
                    'won_opportunities_to_date_amount' => '$10,000.00',
                ],
                'widgetItemPreviousResult' => [
                    'new_opportunities_count' => '+2',
                    'new_opportunities_amount' => '+$60,000.00',
                    'won_opportunities_to_date_count' => '+1',
                    'won_opportunities_to_date_amount' => '+$10,000.00',
                ]
            ],
            'Apply "Custom" date range filter' => [
                'owners' => '',
                'dateRange' => [
                    'type' => AbstractDateFilterType::TYPE_BETWEEN,
                    'start' => '2017-01-01',
                    'end' => date_format($this->createDateTime('+1 day'), 'Y-m-d H:i:s'),
                ],
                'comparePrevious' => true,
                'advancedFilters' => [],
                'widgetItemResult' => [
                    'new_opportunities_count' => 2,
                    'new_opportunities_amount' => '$60,000.00',
                    'won_opportunities_to_date_count' => 1,
                    'won_opportunities_to_date_amount' => '$10,000.00',
                ],
                'widgetItemPreviousResult' => [
                    'new_opportunities_count' => '+2',
                    'new_opportunities_amount' => '+$60,000.00',
                    'won_opportunities_to_date_count' => 'No changes',
                    'won_opportunities_to_date_amount' => '+$0.00'
                ]
            ],
            'Apply advanced filters with owner filter' => [
                'owners' => '1',
                'dateRange' => ['type' => AbstractDateFilterType::TYPE_TODAY],
                'comparePrevious' => false,
                'advancedFilters' => [
                    'entity' => Opportunity::class,
                    'filters' => [
                        'columnName' => 'updatedAt',
                        'criterion' => [
                            'filter' => 'datetime',
                            'data' => [
                                'type' => AbstractDateFilterType::TYPE_BETWEEN,
                                'part' => 'value',
                                'value' => [
                                    'start' => date_format($this->createDateTime('-1 day'), 'Y-m-d H:i:s'),
                                    'end' => date_format($this->createDateTime('+1 day'), 'Y-m-d H:i:s')
                                ]
                            ]
                        ]
                    ]
                ],
                'widgetItemResult' => [
                    'new_opportunities_count' => 0,
                    'new_opportunities_amount' => '$0.00',
                    'won_opportunities_to_date_count' => 0,
                    'won_opportunities_to_date_amount' => '$0.00',
                ],
                'widgetItemPreviousResult' => []
            ],
            'Apply advanced filters without owner filter' => [
                'owners' => '',
                'dateRange' => ['type' => AbstractDateFilterType::TYPE_TODAY],
                'comparePrevious' => true,
                'advancedFilters' => [
                    'entity' => Opportunity::class,
                    'filters' => [
                        'columnName' => 'createdAt',
                        'criterion' => [
                            'filter' => 'datetime',
                            'data' => [
                                'type' => AbstractDateFilterType::TYPE_BETWEEN,
                                'part' => 'value',
                                'value' => [
                                    'start' => date_format($this->createDateTime('-1 day'), 'Y-m-d H:i:s'),
                                    'end' => date_format($this->createDateTime('+1 day'), 'Y-m-d H:i:s')
                                ]
                            ]
                        ]
                    ]
                ],
                'widgetItemResult' => [
                    'new_opportunities_count' => 2,
                    'new_opportunities_amount' => '$60,000.00',
                    'won_opportunities_to_date_count' => 1,
                    'won_opportunities_to_date_amount' => '$10,000.00',
                ],
                'widgetItemPreviousResult' => [
                    'new_opportunities_count' => '+2',
                    'new_opportunities_amount' => '+$60,000.00',
                    'won_opportunities_to_date_count' => 'No changes',
                    'won_opportunities_to_date_amount' => 'No changes'
                ]
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getWidget(): Widget
    {
        return $this->getReference('widget_opportunity_statistics');
    }

    private function inspectResult(array $result, array $previousResult): void
    {
        $response = $this->client->getResponse();
        self::assertEquals(200, $response->getStatusCode(), 'Failed in submit widget configuration options');

        $crawler = $this->client->request(
            'GET',
            $this->getUrl(
                'oro_dashboard_itemized_data_widget',
                [
                    'widget' => 'opportunity_statistics',
                    'bundle' => 'OroDashboard',
                    'name' => 'bigNumbers',
                    '_widgetId' => $this->getWidget()->getId()
                ]
            )
        );

        $response = $this->client->getResponse();
        self::assertEquals(200, $response->getStatusCode(), 'Failed in getting widget view');
        self::assertNotEmpty($crawler->html());

        $newOpportunityCountMetric = $crawler->filterXPath(
            $this->getMetricValueByLabel(
                $this->metrics['new_opportunities_count']
            )
        );
        self::assertEquals(
            $result['new_opportunities_count'],
            $newOpportunityCountMetric->text(),
            '"New Opportunities Count" metric does not much expected value'
        );

        $newOpportunityAmountMetric = $crawler->filterXPath(
            $this->getMetricValueByLabel(
                $this->metrics['new_opportunities_amount']
            )
        );
        self::assertEquals(
            $result['new_opportunities_amount'],
            $newOpportunityAmountMetric->getNode(0)->nodeValue,
            '"New Opportunities Amount" metric does not much expected value'
        );

        $newOpportunityCountMetric = $crawler->filterXPath(
            $this->getMetricValueByLabel(
                $this->metrics['won_opportunities_to_date_count']
            )
        );
        self::assertEquals(
            $result['won_opportunities_to_date_count'],
            $newOpportunityCountMetric->text(),
            '"Won Opportunities Count" metric does not much expected value'
        );

        $newOpportunityCountMetric = $crawler->filterXPath(
            $this->getMetricValueByLabel(
                $this->metrics['won_opportunities_to_date_amount']
            )
        );
        self::assertEquals(
            $result['won_opportunities_to_date_amount'],
            $newOpportunityCountMetric->text(),
            '"Won Opportunities Count" metric does not much expected value'
        );

        $deviationMetric = $crawler->filterXPath(
            $this->getMetricPreviousIntervalValueByLabel(
                $this->metrics['new_opportunities_count']
            )
        );

        if (!empty($previousResult)) {
            self::assertEquals(
                $previousResult['new_opportunities_count'],
                trim($deviationMetric->text()),
                '"New Leads" previous period metric does not much expected value'
            );
        } else {
            self::assertEquals(0, $deviationMetric->count());
        }
    }
}
