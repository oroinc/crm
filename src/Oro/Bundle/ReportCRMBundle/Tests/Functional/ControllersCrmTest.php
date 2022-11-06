<?php

namespace Oro\Bundle\ReportCRMBundle\Tests\Functional;

use Oro\Bundle\ReportBundle\Tests\Functional\ControllersTest as BaseControllersTest;
use Oro\Bundle\ReportCRMBundle\Tests\Functional\DataFixtures\LoadLeadsData;
use Oro\Bundle\ReportCRMBundle\Tests\Functional\DataFixtures\LoadLeadSourceData;

class ControllersCrmTest extends BaseControllersTest
{
    protected function setUp(): void
    {
        $this->initClient(
            [],
            $this->generateBasicAuthHeader()
        );
        $this->client->useHashNavigation(true);
        $this->loadFixtures([LoadLeadSourceData::class, LoadLeadsData::class]);
    }

    /**
     * @dataProvider exportDataProvider
     */
    public function testExport(): void
    {
        $this->markTestSkipped('Skipped by BAP-2946');
    }

    public function exportDataProvider(): array
    {
        return $this->getApiRequestsData(__DIR__ . DIRECTORY_SEPARATOR . 'reports');
    }
}
