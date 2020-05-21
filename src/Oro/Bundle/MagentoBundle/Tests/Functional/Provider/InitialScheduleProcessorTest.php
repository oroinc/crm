<?php
namespace Oro\Bundle\MagentoBundle\Tests\Functional\Provider;

use Oro\Bundle\MagentoBundle\Provider\InitialScheduleProcessor;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

/**
 * @dbIsolationPerTest
 */
class InitialScheduleProcessorTest extends WebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->initClient();
    }

    public function testProcessorCouldBeGetFromContainerAsService()
    {
        $processor = self::getContainer()->get('oro_magento.provider.initial_schedule_processor');

        self::assertInstanceOf(InitialScheduleProcessor::class, $processor);
    }
}
