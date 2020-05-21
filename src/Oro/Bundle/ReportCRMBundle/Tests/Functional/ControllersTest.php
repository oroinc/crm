<?php

namespace Oro\Bundle\ReportCRMBundle\Tests\Functional;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class ControllersTest extends WebTestCase
{
    protected function setUp(): void
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
                'oro_reportcrm_index',
                array(
                    'reportGroupName' => $group,
                    'reportName'      => $report,
                    //'_format'    => 'json'
                )
            )
        );

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        static::assertStringContainsString($reportName, $result->getContent());
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
                'oro_reportcrm-accounts',
                'life_time_value',
                'accounts',
                'Account life time value'
            ),
            'by_opportunities' => array(
                'oro_reportcrm-accounts',
                'by_opportunities',
                'accounts',
                'Accounts by opportunities'
            ),
            'by_status'        => array(
                'oro_reportcrm-opportunities',
                'by_status',
                'opportunities',
                'Opportunities by status'
            ),
            'won_by_period'    => array(
                'oro_reportcrm-opportunities',
                'won_by_period',
                'opportunities',
                'Won opportunities by date period'
            ),
            'total_forecast'    => array(
                'oro_reportcrm-opportunities',
                'total_forecast',
                'opportunities',
                'Forecast'
            ),
            'by_date'          => array(
                'oro_reportcrm-leads',
                'by_date',
                'leads',
                'Number leads by date'
            ),
        );
    }
}
