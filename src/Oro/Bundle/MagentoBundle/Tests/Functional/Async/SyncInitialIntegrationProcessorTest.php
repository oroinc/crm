<?php
namespace Oro\Bundle\MagentoBundle\Tests\Functional\Async;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\MagentoBundle\Async\SyncInitialIntegrationProcessor;

/**
 * @dbIsolationPerTest
 */
class SyncInitialIntegrationProcessorTest extends WebTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->initClient();
    }

    public function testCouldBeGetFromContainerAsService()
    {
        $processor = self::getContainer()->get('orocrm_magento.async.sync_initial_integration_processor');

        self::assertInstanceOf(SyncInitialIntegrationProcessor::class, $processor);
    }
}
