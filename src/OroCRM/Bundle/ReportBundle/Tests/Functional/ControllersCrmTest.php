<?php

namespace OroCRM\Bundle\ReportBundle\Tests\Functional;

use Oro\Bundle\ReportBundle\Tests\Functional\ControllersTest as BaseControllersTest;
use Oro\Bundle\TestFrameworkBundle\Test\ToolsAPI;

/**
 * @outputBuffering enabled
 * @db_isolation
 * @db_reindex
 */
class ControllersCrmTest extends BaseControllersTest
{
    static protected $fixturesLoaded = false;

    public function setUp()
    {
        $this->client = static::createClient(
            array(),
            array_merge(ToolsAPI::generateBasicHeader(), array('HTTP_X-CSRF-Header' => 1))
        );

        if (!self::$fixturesLoaded) {
            $this->client->appendFixtures(__DIR__ . DIRECTORY_SEPARATOR . 'DataFixtures', array('LoadLead'));
            self::$fixturesLoaded = true;
        }
    }

    /**
     * @param array $report
     * @param array $reportResult
     *
     * @dataProvider requestsApi()
     */
    public function testExport($report, $reportResult)
    {
        $this->markTestSkipped("Skipped by BAP-2946");
    }
        /**
     * Data provider for SOAP API tests
     *
     * @return array
     */
    public function requestsApi()
    {
        return ToolsAPI::requestsApi(__DIR__ . DIRECTORY_SEPARATOR . 'reports');
    }
}
