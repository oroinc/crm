<?php

namespace Oro\Bundle\ReportBundle\Tests\Functional;

use Oro\Bundle\ReportBundle\Tests\Functional\ControllersTest as BaseControllersTest;
use Oro\Bundle\TestFrameworkBundle\Test\ToolsAPI;
use Oro\Bundle\TestFrameworkBundle\Test\Client;
use Symfony\Component\DomCrawler\Form;

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
            $disp = $this->client->getKernel()->getContainer()->get('oro_dataaudit.loggable.loggable_manager');
            $disp->setEnabledHandle(false);
            $this->client->appendFixtures(__DIR__ . DIRECTORY_SEPARATOR . 'fixtures', array('LoadLead'));
            self::$fixturesLoaded = true;
            $disp->setEnabledHandle(true);
        }
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
