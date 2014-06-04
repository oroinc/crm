<?php

namespace OroCRM\Bundle\ReportBundle\Tests\Functional;

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
                'OroCRM\Bundle\ReportBundle\Tests\Functional\DataFixtures\LoadLeadSourceData',
                'OroCRM\Bundle\ReportBundle\Tests\Functional\DataFixtures\LoadLeadsData',
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
