<?php

namespace OroCRM\Bundle\ReportBundle\Tests\Functional;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\TestFrameworkBundle\Test\ToolsAPI;
use Oro\Bundle\TestFrameworkBundle\Test\Client;
use Symfony\Component\DomCrawler\Form;

/**
 * @outputBuffering enabled
 * @db_isolation
 * @db_reindex
 */
class ControllersTest extends WebTestCase
{
    /**
     * @var Client
     */
    protected $client;

    public function setUp()
    {
        $this->client = static::createClient(array(), ToolsAPI::generateBasicHeader());
    }

    /**
     * Simple controllers test
     *
     * @param $gridName
     * @param $report
     * @param $group
     * @param $reportName
     * @dataProvider reportsProvider
     */
    public function testIndex($gridName, $report, $group, $reportName)
    {
        $this->client->request(
            'GET',
            $this->client->generate(
                'orocrm_report_index',
                array(
                    'reportGroupName' => $group,
                    'reportName'      => $report,
                    //'_format'    => 'json'
                )
            )
        );

        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200, "text/html; charset=UTF-8");
        $this->assertContains($reportName, $result->getContent());
    }

    /**
     * Simple controllers test
     *
     * @param $gridName
     * @param $report
     * @param $group
     * @dataProvider reportsProvider
     */
    public function testGrid($gridName, $report, $group)
    {
        $reportName = $gridName . '-' . $report;
        $result     = ToolsAPI::getEntityGrid(
            $this->client,
            $reportName,
            array(
                "{$reportName}[reportGroupName]" => $group,
                "{$reportName}[reportName]"      => $report
            )
        );

        ToolsAPI::assertJsonResponse($result, 200);
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
            'by_date'          => array(
                'orocrm_report-leads',
                'by_date',
                'leads',
                'Number leads by date'
            ),
        );
    }
}
