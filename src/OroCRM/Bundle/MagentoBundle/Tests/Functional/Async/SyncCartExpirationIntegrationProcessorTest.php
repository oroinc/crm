<?php
namespace OroCRM\Bundle\MagentoBundle\Tests\Functional\Async;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use OroCRM\Bundle\MagentoBundle\Async\SyncCartExpirationIntegrationProcessor;

class SyncCartExpirationIntegrationProcessorTest extends WebTestCase
{
    public function testCouldBeGetFromContainerAsService()
    {
        $processor = self::getContainer()->get('orocrm_magento.async.sync_cart_expiration_integration_processor');

        self::assertInstanceOf(SyncCartExpirationIntegrationProcessor::class, $processor);
    }
}
