<?php
namespace Oro\Bundle\MagentoBundle\Tests\Functional\Async;

use Oro\Bundle\MagentoBundle\Async\SyncInitialIntegrationProcessor;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @dbIsolationPerTest
 */
class SyncInitialIntegrationProcessorTest extends WebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->initClient();
    }

    public function testCouldBeGetFromContainerAsService()
    {
        $processor = self::getContainer()->get('oro_magento.async.sync_initial_integration_processor');

        self::assertInstanceOf(SyncInitialIntegrationProcessor::class, $processor);
    }
}
