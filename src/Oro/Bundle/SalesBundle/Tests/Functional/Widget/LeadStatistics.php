<?php

namespace Oro\Bundle\SalesBundle\Tests\Functional\Widget;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Field\InputFormField;
use Symfony\Component\DomCrawler\Form;

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
        $this->createAdditionalDateRandgeFormElements($form);
        $this->createMetricsElements($form);
        $form['lead_statistics[dateRange][part]'] = 'value';
        $form['lead_statistics[dateRange][type]'] = AbstractDateFilterType::TYPE_ALL_TIME;
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
            $openLeadsMetric->getNode(0)->nodeValue,
            0,
            '"Open Leads" metric doesn\'t much expected value !'
        );
        $onewLeadsMetric = $crawler->filterXPath(
            $this->getMetricValueByLabel(
                $this->metrics['new_leads_count']
            )
        );
        $this->assertEquals(
            $onewLeadsMetric->getNode(0)->nodeValue,
            0,
            '"New Leads" metric doesn\'t much expected value !'
        );
    }

    public function wudgetProvider()
    {
        return [
            'Apply owner filter with date range filter on today' => [
                'owners' => '',
                'dateRange' => '',
                'advancedFilters' => '',
                'widgetItemResult' => [
                    'open_leads_count' => '',
                    'new_leads_count' => ''
                ],
                'widgetItemPreviousResult' => [
                    'open_leads_count' => '',
                    'new_leads_count' => ''
                ]
            ],
            'Apply "All time" date range filter' => [],
            'Apply "Today" date range filter' => [],
            'Apply "Custom" date range filter' => [],
            'Apply advanced filters with owner filter' => [],
            'Apply advanced filters without owner filter' => [],
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
     * Create fields of 'WidgetConfigDateRangeFilter' component
     *
     * @param Form $form
     */
    protected function createAdditionalDateRandgeFormElements(Form $form)
    {
        $doc = new \DOMDocument("1.0");
        $doc->loadHTML(
            '<input type="text" name="lead_statistics[dateRange][type]" value="" />' .
            '<input type="text" name="lead_statistics[dateRange][value][start]" value="" />' .
            '<input type="text" name="lead_statistics[dateRange][value][end]" value="" />'
        );

        $dateRangeTypeField = new InputFormField($doc->getElementsByTagName('input')->item(0));
        $form->set($dateRangeTypeField);
        $dateRangeTypeField = new InputFormField($doc->getElementsByTagName('input')->item(1));
        $form->set($dateRangeTypeField);
        $dateRangeTypeField = new InputFormField($doc->getElementsByTagName('input')->item(2));
        $form->set($dateRangeTypeField);
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
                '<input type="text" name="lead_statistics[subWidgets][items][%1$s][show]" value="on" />',
                $index,
                $metricsKeys[$index]
            );
        }

        $doc->loadHTML($html);

        for ($index=0; $index < $metricsCount; $index++) {
            $dateRangeTypeField = new InputFormField($doc->getElementsByTagName('input')->item(0 + $index * 3));
            $form->set($dateRangeTypeField);
            $dateRangeTypeField = new InputFormField($doc->getElementsByTagName('input')->item(1 + $index * 3));
            $form->set($dateRangeTypeField);
            $dateRangeTypeField = new InputFormField($doc->getElementsByTagName('input')->item(2 + $index * 3));
            $form->set($dateRangeTypeField);
        }
    }

    /**
     * @return Widget
     */
    protected function getWidget()
    {
        return $this->getReference('widget_lead_statistics');
    }

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
}
