<?php

namespace Oro\Bundle\ChannelBundle\Tests\Functional\Async;

use Oro\Bundle\ChannelBundle\Async\ChangeIntegrationStatusProcessor;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @dbIsolationPerTest
 */
class ChangeIntegrationStatusProcessorTest extends WebTestCase
{
    protected function setUp(): void
    {
        $this->initClient();
    }

    public function testCouldBeGetFromContainerAsService(): void
    {
        $processor = self::getContainer()->get('oro_channel.async.change_integration_status_processor');

        self::assertInstanceOf(ChangeIntegrationStatusProcessor::class, $processor);
    }
}
