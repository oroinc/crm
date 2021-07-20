<?php

namespace Oro\Bundle\SalesBundle\Tests\Functional\Widget;

use Oro\Bundle\DashboardBundle\Entity\Widget;
use Oro\Bundle\FilterBundle\Form\Type\Filter\AbstractDateFilterType;
use Oro\Bundle\SalesBundle\Tests\Functional\Fixture\LoadOpportunityStatisticsWidgetFixture;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;

class OpportunityStatisticsTest extends BaseStatistics
{
    /** @var array */
    protected $metrics = [
        'new_opportunities_count'          => 'New Opportunities count',
        'new_opportunities_amount'         => 'New Opportunities amount',
        'won_opportunities_to_date_count'  => 'Won Opportunities to date count',
        'won_opportunities_to_date_amount' => 'Won Opportunities to date amount'
    ];

    protected function setUp(): void
    {
        $this->initClient([], $this->generateBasicAuthHeader());
        $this->loadFixtures([
            LoadOpportunityStatisticsWidgetFixture::class
        ]);
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
        $this->assertEquals($response->getStatusCode(), 200, 'Failed in submit widget configuration options !');

        $crawler = $this->client->request(
            'GET',
            $this->getUrl(
                'oro_dashboard_itemized_data_widget',
                [
                    'widget' => 'opportunity_statistics',
                    'bundle' => 'OroDashboardBundle',
                    'name' => 'bigNumbers',
                    '_widgetId' => $this->getWidget()->getId()
                ]
            )
        );

        $response = $this->client->getResponse();
        $this->assertEquals($response->getStatusCode(), 200, 'Failed in getting widget view !');
        $this->assertNotEmpty($crawler->html());

        $newOpportunityCountMetric = $crawler->filterXPath(
            $this->getMetricValueByLabel(
                $this->metrics['new_opportunities_count']
            )
        );
        $this->assertEquals(
            $newOpportunityCountMetric->text(),
            2,
            '"New Opportunities Count" metric doesn\'t much expected value !'
        );

        $newOpportunityAmountMetric = $crawler->filterXPath(
            $this->getMetricValueByLabel(
                $this->metrics['new_opportunities_amount']
            )
        );
        $this->assertEquals(
            $newOpportunityAmountMetric->getNode(0)->nodeValue,
            '$60,000.00',
            '"New Opportunities Amount" metric doesn\'t much expected value !'
        );

        $newOpportunityCountMetric = $crawler->filterXPath(
            $this->getMetricValueByLabel(
                $this->metrics['won_opportunities_to_date_count']
            )
        );
        $this->assertEquals(
            $newOpportunityCountMetric->text(),
            1,
            '"Won Opportunities Count" metric doesn\'t much expected value !'
        );

        $newOpportunityCountMetric = $crawler->filterXPath(
            $this->getMetricValueByLabel(
                $this->metrics['won_opportunities_to_date_amount']
            )
        );
        $this->assertEquals(
            $newOpportunityCountMetric->text(),
            '$10,000.00',
            '"Won Opportunities Count" metric doesn\'t much expected value !'
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

        /**
         * @var $crawler Crawler
         * @var $form Form
         */
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
                    'entity' => 'Oro\Bundle\SalesBundle\Entity\Opportunity',
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
                    'type' => AbstractDateFilterType::TYPE_MORE_THAN,
                    'start' => '2017-01-01',
                    'end' => ''
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
                    'entity' => 'Oro\Bundle\SalesBundle\Entity\Opportunity',
                    'filters' => [
                        'columnName' => 'updatedAt',
                        'criterion' => [
                            'filter' => 'datetime',
                            'data' => [
                                'type' => strval(AbstractDateFilterType::TYPE_BETWEEN),
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
                    'entity' => 'Oro\Bundle\SalesBundle\Entity\Opportunity',
                    'filters' => [
                        'columnName' => 'createdAt',
                        'criterion' => [
                            'filter' => 'datetime',
                            'data' => [
                                'type' => strval(AbstractDateFilterType::TYPE_BETWEEN),
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

    protected function inspectResult(array $result, array $previousResult): void
    {
        $response = $this->client->getResponse();
        $this->assertEquals($response->getStatusCode(), 200, "Failed in submit widget configuration options !");

        $crawler = $this->client->request(
            'GET',
            $this->getUrl(
                'oro_dashboard_itemized_data_widget',
                [
                    'widget' => 'opportunity_statistics',
                    'bundle' => 'OroDashboardBundle',
                    'name' => 'bigNumbers',
                    '_widgetId' => $this->getWidget()->getId()
                ]
            )
        );

        $response = $this->client->getResponse();
        $this->assertEquals($response->getStatusCode(), 200, "Failed in gettting widget view !");
        $this->assertNotEmpty($crawler->html());

        $newOpportunityCountMetric = $crawler->filterXPath(
            $this->getMetricValueByLabel(
                $this->metrics['new_opportunities_count']
            )
        );
        $this->assertEquals(
            $newOpportunityCountMetric->text(),
            $result['new_opportunities_count'],
            '"New Opportunities Count" metric doesn\'t much expected value !'
        );

        $newOpportunityAmountMetric = $crawler->filterXPath(
            $this->getMetricValueByLabel(
                $this->metrics['new_opportunities_amount']
            )
        );
        $this->assertEquals(
            $newOpportunityAmountMetric->getNode(0)->nodeValue,
            $result['new_opportunities_amount'],
            '"New Opportunities Amount" metric doesn\'t much expected value !'
        );

        $newOpportunityCountMetric = $crawler->filterXPath(
            $this->getMetricValueByLabel(
                $this->metrics['won_opportunities_to_date_count']
            )
        );
        $this->assertEquals(
            $newOpportunityCountMetric->text(),
            $result['won_opportunities_to_date_count'],
            '"Won Opportunities Count" metric doesn\'t much expected value !'
        );

        $newOpportunityCountMetric = $crawler->filterXPath(
            $this->getMetricValueByLabel(
                $this->metrics['won_opportunities_to_date_amount']
            )
        );
        $this->assertEquals(
            $newOpportunityCountMetric->text(),
            $result['won_opportunities_to_date_amount'],
            '"Won Opportunities Count" metric doesn\'t much expected value !'
        );

        $deviationMetric = $crawler->filterXPath(
            $this->getMetricPreviousIntervalValueByLabel(
                $this->metrics['new_opportunities_count']
            )
        );

        if (!empty($previousResult)) {
            $this->assertEquals(
                trim($deviationMetric->text()),
                $previousResult['new_opportunities_count'],
                '"New Leads" previous period metric doesn\'t much expected value !'
            );
        } else {
            $this->assertEquals(0, $deviationMetric->count());
        }
    }
}
