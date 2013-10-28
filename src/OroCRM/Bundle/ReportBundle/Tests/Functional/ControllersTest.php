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
     * @param $group
     * @param $report
     * @dataProvider reportsProvider
     */
    public function testIndex($group, $report)
    {
        $this->client->request(
            'GET',
            $this->client->generate(
                'orocrm_report_index',
                array(
                    'reportGroupName' => $group,
                    'reportName' => $report,
                    '_format'    => 'json'
                )
            )
        );
        $result = $this->client->getResponse();
        ToolsAPI::assertJsonResponse($result, 200);
    }

    public function reportsProvider()
    {
        return array(
            'life_time_value' => array('accounts', 'life_time_value'),
            'by_opportunities' => array('accounts', 'by_opportunities'),
            'by_step' => array('opportunities', 'by_step'),
            'won_by_period' => array('opportunities', 'won_by_period'),
            'by_date' => array('leads', 'by_date'),
        );
    }
}
