<?php

namespace Oro\Bundle\ReportCRMBundle\Tests\Functional;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @outputBuffering enabled
 * @dbIsolation
 * @dbReindex
 */
class CommandsTest extends WebTestCase
{
    protected function setUp()
    {
        $this->initClient();
        $this->loadFixtures(
            array(
                'Oro\Bundle\ReportCRMBundle\Tests\Functional\DataFixtures\LoadLeadSourceData',
                'Oro\Bundle\ReportCRMBundle\Tests\Functional\DataFixtures\LoadLeadsData',
            )
        );
    }

    public function testReportUpdate()
    {
        $result = $this->runCommand('oro:report:update');

        $this->assertEquals(
            "Update report transactional tables" . PHP_EOL ."Completed" . PHP_EOL,
            $result
        );
    }
}
