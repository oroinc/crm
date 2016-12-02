<?php

namespace Oro\Bundle\SalesBundle\Tests\Functional\Widget;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;

use Oro\Bundle\DashboardBundle\Entity\Widget;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\FilterBundle\Form\Type\Filter\AbstractDateFilterType;

/**
 * @dbIsolationPerTest
 */
class LeadStatistics extends WebTestCase
{
    public function setUp()
    {
        $this->initClient(
            ['debug' => false],
            array_merge($this->generateBasicAuthHeader(), array('HTTP_X-CSRF-Header' => 1))
        );
        $this->client->useHashNavigation(true);
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
        $form['lead_statistics[dateRange][part]'] = AbstractDateFilterType::TYPE_ALL_TIME;
//        $form['lead_statistics[dateRange][type]'] = AbstractDateFilterType::TYPE_ALL_TIME;
        $form['lead_statistics[usePreviousInterval]'] = 1;
        $crawler = $this->client->submit($form);

        $response = $this->client->getResponse();
        $this->assertEquals($response->getStatusCode(), 200);
        $this->assertNotEmpty($crawler->html());

        $this->client->useHashNavigation(false);
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
        $this->client->useHashNavigation(true);

        $response = $this->client->getResponse();
        $this->assertEquals($response->getStatusCode(), 200);
        $this->assertNotEmpty($crawler->html());

        $openLeadsMetric = $crawler->filterXPath($this->getMetricValueByLabel('Open Leads'));
        $this->assertEquals($openLeadsMetric->getNode(0)->nodeValue, 0);
        $onewLeadsMetric = $crawler->filterXPath($this->getMetricValueByLabel('New Leads'));
        $this->assertEquals($onewLeadsMetric->getNode(0)->nodeValue, 0);
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
        $this->assertEquals($response->getStatusCode(), 200);
    }
}
