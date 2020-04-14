<?php
namespace Oro\Bundle\MagentoBundle\Tests\Functional\Async;

use Oro\Bundle\MagentoBundle\Async\SyncCartExpirationIntegrationProcessor;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @dbIsolationPerTest
 */
class SyncCartExpirationIntegrationProcessorTest extends WebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->initClient();
    }

    public function testCouldBeGetFromContainerAsService()
    {
        $processor = self::getContainer()->get('oro_magento.async.sync_cart_expiration_integration_processor');

        self::assertInstanceOf(SyncCartExpirationIntegrationProcessor::class, $processor);
    }
}
