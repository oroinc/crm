<?php

namespace Oro\Bundle\ReportCRMBundle\Tests\Functional;

use Oro\Bundle\ReportBundle\Tests\Functional\ControllersTest as BaseControllersTest;

class ControllersCrmTest extends BaseControllersTest
{
    protected function setUp(): void
    {
        $this->initClient(
            array(),
            $this->generateBasicAuthHeader()
        );
        $this->client->useHashNavigation(true);
        $this->loadFixtures(
            array(
                'Oro\Bundle\ReportCRMBundle\Tests\Functional\DataFixtures\LoadLeadSourceData',
                'Oro\Bundle\ReportCRMBundle\Tests\Functional\DataFixtures\LoadLeadsData',
            )
        );
    }

    /**
     * @dataProvider exportDataProvider
     */
    public function testExport()
    {
        $this->markTestSkipped("Skipped by BAP-2946");
    }

    /**
     * Data provider for SOAP API tests
     *
     * @return array
     */
    public function exportDataProvider()
    {
        return $this->getApiRequestsData(__DIR__ . DIRECTORY_SEPARATOR . 'reports');
    }
}
