<?php

namespace Oro\Bundle\ReportCRMBundle\Tests\Functional;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class ControllersTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient([], $this->generateBasicAuthHeader());
        $this->client->useHashNavigation(true);
    }

    /**
     * @dataProvider reportsProvider
     */
    public function testIndex(string $gridName, string $report, string $group, string $reportName)
    {
        $this->client->request(
            'GET',
            $this->getUrl(
                'oro_reportcrm_index',
                [
                    'reportGroupName' => $group,
                    'reportName'      => $report,
                    //'_format'    => 'json'
                ]
            )
        );

        $result = $this->client->getResponse();
        $this->assertHtmlResponseStatusCodeEquals($result, 200);
        self::assertStringContainsString($reportName, $result->getContent());
    }

    /**
     * @dataProvider reportsProvider
     */
    public function testGrid(string $gridName, string $report, string $group)
    {
        $reportName = $gridName . '-' . $report;
        $response = $this->client->requestGrid(
            $reportName,
            [
                "{$reportName}[reportGroupName]" => $group,
                "{$reportName}[reportName]"      => $report
            ]
        );

        $this->assertJsonResponseStatusCodeEquals($response, 200);
    }

    public function reportsProvider(): array
    {
        return [
            'life_time_value'  => [
                'oro_reportcrm-accounts',
                'life_time_value',
                'accounts',
                'Account life time value'
            ],
            'by_opportunities' => [
                'oro_reportcrm-accounts',
                'by_opportunities',
                'accounts',
                'Accounts by opportunities'
            ],
            'by_status'        => [
                'oro_reportcrm-opportunities',
                'by_status',
                'opportunities',
                'Opportunities by status'
            ],
            'won_by_period'    => [
                'oro_reportcrm-opportunities',
                'won_by_period',
                'opportunities',
                'Won opportunities by date period'
            ],
            'total_forecast'    => [
                'oro_reportcrm-opportunities',
                'total_forecast',
                'opportunities',
                'Forecast'
            ],
            'by_date'          => [
                'oro_reportcrm-leads',
                'by_date',
                'leads',
                'Number leads by date'
            ],
        ];
    }
}
