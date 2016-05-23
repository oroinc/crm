<?php

namespace OroCRM\Bundle\ReportBundle\Tests\Functional;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @outputBuffering enabled
 * @dbIsolation
 * @dbReindex
 */
class ControllersTest extends WebTestCase
{
    protected function setUp()
    {
        $this->initClient(array(), $this->generateBasicAuthHeader());
        $this->client->useHashNavigation(true);
    }

    /**
     * Simple controllers test
     *
     * @param string $gridName
     * @param string $report
     * @param string $group
     * @param string $reportName
     * @dataProvider reportsProvider
     */
    public function testIndex($gridName, $report, $group, $reportName)
    {
        $this->client->request(
            'GET',
            $this->getUrl(
                'orocrm_report_index',
                array(
                    'reportGroupName' => $group,
                    'reportName'      => $report,
                    //'_format'    => 'json'
                )
            )
        );

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        $this->assertContains($reportName, $result->getContent());
    }

    /**
     * Simple controllers test
     *
     * @param string $gridName
     * @param string $report
     * @param string $group
     * @dataProvider reportsProvider
     */
    public function testGrid($gridName, $report, $group)
    {
        $reportName = $gridName . '-' . $report;
        $response = $this->client->requestGrid(
            $reportName,
            array(
                "{$reportName}[reportGroupName]" => $group,
                "{$reportName}[reportName]"      => $report
            )
        );

        $this->assertJsonResponseStatusCodeEquals($response, 200);
    }

    public function reportsProvider()
    {
        return array(
            'life_time_value'  => array(
                'orocrm_report-accounts',
                'life_time_value',
                'accounts',
                'Account life time value'
            ),
            'by_opportunities' => array(
                'orocrm_report-accounts',
                'by_opportunities',
                'accounts',
                'Accounts by opportunities'
            ),
            'by_status'        => array(
                'orocrm_report-opportunities',
                'by_status',
                'opportunities',
                'Opportunities by status'
            ),
            'won_by_period'    => array(
                'orocrm_report-opportunities',
                'won_by_period',
                'opportunities',
                'Won opportunities by date period'
            ),
            'total_forecast'    => array(
                'orocrm_report-opportunities',
                'total_forecast',
                'opportunities',
                'Forecast'
            ),
            'by_date'          => array(
                'orocrm_report-leads',
                'by_date',
                'leads',
                'Number leads by date'
            ),
        );
    }
}
