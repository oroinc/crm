<?php

namespace Oro\Bundle\SalesBundle\Tests\Functional\Widget;

use Symfony\Component\DomCrawler\Form;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Field\InputFormField;
use Symfony\Component\DomCrawler\Field\ChoiceFormField;

use Oro\Bundle\DashboardBundle\Entity\Widget;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\FilterBundle\Form\Type\Filter\AbstractDateFilterType;

/**
 * @dbIsolationPerTest
 */
class LeadStatistics extends WebTestCase
{
    protected $metrics = [
        'open_leads_count' => 'Open Leads',
        'new_leads_count' => 'New Leads'
    ];

    public function setUp()
    {
        $this->initClient(
            ['debug' => false],
            array_merge($this->generateBasicAuthHeader(), array('HTTP_X-CSRF-Header' => 1))
        );
        $this->loadFixtures([
            'Oro\Bundle\SalesBundle\Tests\Functional\Fixture\LoadLeadStatisticsWidgetFixture'
        ]);
    }

    public function testGetWidgetConfigureDialog()
    {
        $this->getConfigureDialog();
    }

    /**
     * @depends testGetWidgetConfigureDialog
     */
    public function testDefaultConfiguration()
    {
        $this->getConfigureDialog();

        /**
         * @var $crawler Crawler
         * @var $form Form
         */
        $crawler = $this->client->getCrawler();
        $form = $crawler->selectButton('Save')->form();
        $this->createAndSetDateRangeFormElements($form, ['type' => AbstractDateFilterType::TYPE_ALL_TIME]);
        $this->createMetricsElements($form);
        $form['lead_statistics[usePreviousInterval]'] = 1;
        $this->client->submit($form);

        $response = $this->client->getResponse();
        $this->assertEquals($response->getStatusCode(), 200, "Failed in submit widget configuration options !");

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
        $this->assertEquals($response->getStatusCode(), 200, "Failed in gettting widget view !");
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
        $owners,
        $dateRange,
        $comparePrevious,
        $advancedFilters,
        $result,
        $previousResult
    ) {
        $this->getConfigureDialog();

        /**
         * @var $crawler Crawler
         * @var $form Form
         */
        $crawler = $this->client->getCrawler();
        $form = $crawler->selectButton('Save')->form();
        $form['lead_statistics[owners][users]'] = $owners;
        $this->createMetricsElements($form);
        $this->setComparePrevious($form, $comparePrevious);
        $this->setAdvancedFilters($form, $advancedFilters);
        $this->createAndSetDateRangeFormElements($form, $dateRange);

        $this->client->submit($form);

        $this->inspectResult($result, $previousResult);
    }

    /**
     * @return array
     */
    public function widgetProvider()
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
     * @param string $label
     *
     * @return string
     */
    protected function getMetricValueByLabel($label)
    {
        return sprintf('//*[text() = "%s"]/following-sibling::h3[@class="value"]', $label);
    }

    /**
     * @param string $label
     *
     * @return string
     */
    protected function getMetricPreviousIntervalValueByLabel($label)
    {
        return sprintf('//*[text() = "%s"]/following-sibling::div[@class="deviation"][position()=1]/span', $label);
    }

    /**
     * Create and set fields of 'WidgetConfigDateRangeFilter' component
     *
     * @param Form $form
     * @param array|null $data
     */
    protected function createAndSetDateRangeFormElements(Form $form, $data = null)
    {
        $doc = new \DOMDocument("1.0");
        $inputs = '<input type="text" name="lead_statistics[dateRange][type]" value="" />';

        if (!empty($data) && count($data) > 1) {
            $inputs .= '<input type="text" name="lead_statistics[dateRange][value][start]" value="" />' .
                       '<input type="text" name="lead_statistics[dateRange][value][end]" value="" />';
        }
        $doc->loadHTML($inputs);

        $index = 0;
        foreach ($data as $key => $value) {
            $dateRangeTypeField = new InputFormField($doc->getElementsByTagName('input')->item($index));
            $form->set($dateRangeTypeField);

            $fieldName = $index > 0
                ? 'lead_statistics[dateRange][value]['.$key.']'
                : 'lead_statistics[dateRange]['.$key.']' ;

            $form[$fieldName] = $value;
            $index++;
        }

        $form['lead_statistics[dateRange][part]'] = 'value';
    }

    /**
     * Create fields of 'ItemsView' component
     *
     * @param Form $form
     */
    protected function createMetricsElements(Form $form)
    {
        $doc = new \DOMDocument("1.0");
        $metricsKeys = array_keys($this->metrics);
        $metricsCount = count($metricsKeys);
        $html = '';
        for ($index=0; $index < $metricsCount; $index++) {
            $html .= sprintf(
                '<input type="text" name="lead_statistics[subWidgets][items][%1$s][id]" value="%2$s" />' .
                '<input type="text" name="lead_statistics[subWidgets][items][%1$s][order]" value="%1$s" />' .
                '<input type="checkbox" name="lead_statistics[subWidgets][items][%1$s][show]" checked />',
                $index,
                $metricsKeys[$index]
            );
        }

        $doc->loadHTML($html);

        for ($index=0; $index < $metricsCount; $index++) {
            $subItemTypeField = new InputFormField($doc->getElementsByTagName('input')->item(0 + $index * 3));
            $form->set($subItemTypeField);
            $subItemTypeField = new InputFormField($doc->getElementsByTagName('input')->item(1 + $index * 3));
            $form->set($subItemTypeField);
            $subItemTypeField = new ChoiceFormField($doc->getElementsByTagName('input')->item(2 + $index * 3));
            $form->set($subItemTypeField);
        }
    }

    /**
     * @return Widget
     */
    protected function getWidget()
    {
        return $this->getReference('widget_lead_statistics');
    }

    /**
     * Request widget configuration form
     */
    protected function getConfigureDialog()
    {
        $this->client->request(
            'GET',
            $this->getUrl(
                'oro_dashboard_configure',
                ['id' => $this->getWidget()->getId(), '_widgetContainer' => 'dialog']
            )
        );

        $response = $this->client->getResponse();
        $this->assertEquals($response->getStatusCode(), 200, 'Failed in getting configure widget dialog window !');
    }

    /**
     * @param Form $form
     * @param bool $comparePrevious
     */
    protected function setComparePrevious(Form $form, $comparePrevious)
    {
        $form->remove('lead_statistics[usePreviousInterval]');
        if ($comparePrevious) {

            $doc = new \DOMDocument("1.0");
            $doc->loadHTML('<input type="text" name="lead_statistics[usePreviousInterval]" value="1" checked="checked"/>');
            $compareToPreviousField = new InputFormField($doc->getElementsByTagName('input')->item(0));
            $form->set($compareToPreviousField);
            $form['lead_statistics[usePreviousInterval]'] = 1;
        }
    }

    protected function setAdvancedFilters(Form $form, array $advancedFilters)
    {
        if (!empty($advancedFilters)) {
            $filters = json_encode($advancedFilters['filters']);
            $form['lead_statistics[queryFilter][entity]'] = $advancedFilters['entity'];
            $form['lead_statistics[queryFilter][definition]'] = '{"filters":['.$filters.']}';
        }
    }

    /**
     * @param array $result
     * @param array $previousResult
     */
    protected function inspectResult(array $result, array $previousResult)
    {
        $response = $this->client->getResponse();
        $this->assertEquals($response->getStatusCode(), 200, "Failed in submit widget configuration options !");

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

        $this->assertEquals($response->getStatusCode(), 200, "Failed in gettting widget view !");
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

    /**
     * @param string $modifyStr
     * @return \DateTime
     */
    protected function createDateTime($modifyStr)
    {
        $date = new \DateTime('now', new \DateTimeZone('UTC'));
        $date->modify($modifyStr);

        return $date;
    }
}
