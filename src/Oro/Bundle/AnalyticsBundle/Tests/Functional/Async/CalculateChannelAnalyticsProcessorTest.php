<?php
namespace Oro\Bundle\AnalyticsBundle\Tests\Functional\Async;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\AnalyticsBundle\Async\CalculateChannelAnalyticsProcessor;

/**
 * @dbIsolationPerTest
 */
class CalculateChannelAnalyticsProcessorTest extends WebTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->initClient();
    }

    public function testCouldBeGetFromContainerAsService()
    {
        $processor = $this->getContainer()->get('oro_analytics.async.calculate_channel_analytics_processor');

        $this->assertInstanceOf(CalculateChannelAnalyticsProcessor::class, $processor);
    }
}
