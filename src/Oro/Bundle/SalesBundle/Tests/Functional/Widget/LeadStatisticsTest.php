<?php

namespace Oro\Bundle\SalesBundle\Tests\Functional\Widget;

use Oro\Bundle\DashboardBundle\Entity\Widget;
use Oro\Bundle\FilterBundle\Form\Type\Filter\AbstractDateFilterType;
use Oro\Bundle\SalesBundle\Tests\Functional\Fixture\LoadLeadStatisticsWidgetFixture;

class LeadStatisticsTest extends BaseStatistics
{
    /** @var array */
    protected $metrics = [
        'open_leads_count' => 'Open Leads',
        'new_leads_count' => 'New Leads'
    ];

    protected function setUp(): void
    {
        $this->initClient([], $this->generateBasicAuthHeader());
        $this->loadFixtures([
            LoadLeadStatisticsWidgetFixture::class
        ]);
    }

    public function testDefaultConfiguration(): void
    {
        $this->getConfigureDialog();

        $crawler = $this->client->getCrawler();

        $form = $crawler->selectButton('Save')->form();

        $data = $form->getPhpValues();

        $this->createMetricsElements($data, 'lead_statistics');
        $this->setComparePrevious($data, 'lead_statistics', true);
        $this->createAndSetDateRangeFormElements($data, 'lead_statistics', [
            'type' => AbstractDateFilterType::TYPE_ALL_TIME
        ]);

        $this->client->request($form->getMethod(), $form->getUri(), $data);

        $response = $this->client->getResponse();
        $this->assertEquals($response->getStatusCode(), 200, 'Failed in submit widget configuration options!');

        $crawler = $this->client->request(
            'GET',
            $this->getUrl(
                'oro_dashboard_itemized_data_widget',
                [
                    'widget' => 'lead_statistics',
                    'bundle' => 'OroDashboardBundle',
                    'name' => 'bigNumbers',
                    '_widgetId' => $this->getWidget()->getId()
                ]
            )
        );

        $response = $this->client->getResponse();
        $this->assertEquals($response->getStatusCode(), 200, "Failed in getting widget view!");
        $this->assertNotEmpty($crawler->html());

        $openLeadsMetric = $crawler->filterXPath(
            $this->getMetricValueByLabel(
                $this->metrics['open_leads_count']
            )
        );
        $this->assertEquals(
            $openLeadsMetric->text(),
            1,
            '"Open Leads" metric doesn\'t much expected value !'
        );
        $newLeadsMetric = $crawler->filterXPath(
            $this->getMetricValueByLabel(
                $this->metrics['new_leads_count']
            )
        );
        $this->assertEquals(
            $newLeadsMetric->getNode(0)->nodeValue,
            1,
            '"New Leads" metric doesn\'t much expected value !'
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
    ) {
        $this->getConfigureDialog();

        $crawler = $this->client->getCrawler();
        $form = $crawler->selectButton('Save')->form();

        $data = $form->getPhpValues();
        $data['lead_statistics']['owners']['users'] = $owners;

        $this->createMetricsElements($data, 'lead_statistics');
        $this->setComparePrevious($data, 'lead_statistics', $comparePrevious);
        $this->setAdvancedFilters($data, 'lead_statistics', $advancedFilters);
        $this->createAndSetDateRangeFormElements($data, 'lead_statistics', $dateRange);

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
                    'entity' => 'Oro\Bundle\SalesBundle\Entity\Lead',
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
                    'open_leads_count' => 0,
                    'new_leads_count' => 0
                ],
                'widgetItemPreviousResult' => ['new_leads_count' => 'No changes']
            ],
            'Apply "All time" date range filter' => [
                'owners' => '',
                'dateRange' => ['type' => AbstractDateFilterType::TYPE_ALL_TIME],
                'comparePrevious' => true,
                'advancedFilters' => [],
                'widgetItemResult' => [
                    'open_leads_count' => 1,
                    'new_leads_count' => 1
                ],
                'widgetItemPreviousResult' => []
            ],
            'Apply "Today" date range filter' => [
                'owners' => '',
                'dateRange' => ['type' => AbstractDateFilterType::TYPE_TODAY],
                'comparePrevious' => true,
                'advancedFilters' => [],
                'widgetItemResult' => [
                    'open_leads_count' => 1,
                    'new_leads_count' => 1
                ],
                'widgetItemPreviousResult' => ['new_leads_count' => '+1']
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
                'widgetItemResult' => ['open_leads_count' => 1, 'new_leads_count' => 1],
                'widgetItemPreviousResult' => ['new_leads_count' => '+1']
            ],
            'Apply advanced filters with owner filter' => [
                'owners' => '1',
                'dateRange' => ['type' => AbstractDateFilterType::TYPE_TODAY],
                'comparePrevious' => false,
                'advancedFilters' => [
                    'entity' => 'Oro\Bundle\SalesBundle\Entity\Lead',
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
                    'open_leads_count' => 0,
                    'new_leads_count' => 0
                ],
                'widgetItemPreviousResult' => []
            ],
            'Apply advanced filters without owner filter' => [
                'owners' => '',
                'dateRange' => ['type' => AbstractDateFilterType::TYPE_TODAY],
                'comparePrevious' => true,
                'advancedFilters' => [
                    'entity' => 'Oro\Bundle\SalesBundle\Entity\Lead',
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
                    'open_leads_count' => 1,
                    'new_leads_count' => 1
                ],
                'widgetItemPreviousResult' => ['new_leads_count' => '+1']
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getWidget(): Widget
    {
        return $this->getReference('widget_lead_statistics');
    }

    protected function inspectResult(array $result, array $previousResult): void
    {
        $response = $this->client->getResponse();
        $this->assertEquals($response->getStatusCode(), 200, 'Failed in submit widget configuration options !');

        $crawler = $this->client->request(
            'GET',
            $this->getUrl(
                'oro_dashboard_itemized_data_widget',
                [
                    'widget' => 'lead_statistics',
                    'bundle' => 'OroDashboardBundle',
                    'name' => 'bigNumbers',
                    '_widgetId' => $this->getWidget()->getId()
                ]
            )
        );

        $response = $this->client->getResponse();

        $this->assertEquals($response->getStatusCode(), 200, 'Failed in getting widget view !');
        $this->assertNotEmpty($crawler->html());

        $openLeadsMetric = $crawler->filterXPath(
            $this->getMetricValueByLabel(
                $this->metrics['open_leads_count']
            )
        );
        $this->assertEquals(
            $openLeadsMetric->text(),
            $result['open_leads_count'],
            '"Open Leads" metric doesn\'t much expected value !'
        );

        $newLeadsMetric = $crawler->filterXPath(
            $this->getMetricValueByLabel(
                $this->metrics['new_leads_count']
            )
        );
        $this->assertEquals(
            $newLeadsMetric->text(),
            $result['new_leads_count'],
            '"New Leads" metric doesn\'t much expected value !'
        );

        $deviationMetric = $crawler->filterXPath(
            $this->getMetricPreviousIntervalValueByLabel(
                $this->metrics['new_leads_count']
            )
        );

        if (!empty($previousResult)) {
            $this->assertEquals(
                trim($deviationMetric->text()),
                $previousResult['new_leads_count'],
                '"New Leads" previous period metric doesn\'t much expected value !'
            );
        } else {
            $this->assertEquals(0, $deviationMetric->count());
        }
    }
}
