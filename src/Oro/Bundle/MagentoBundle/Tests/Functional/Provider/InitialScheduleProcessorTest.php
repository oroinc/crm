<?php
namespace Oro\Bundle\MagentoBundle\Tests\Functional\Provider;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\MagentoBundle\Provider\InitialScheduleProcessor;

/**
 * @dbIsolationPerTest
 */
class InitialScheduleProcessorTest extends WebTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->initClient();
    }

    public function testProcessorCouldBeGetFromContainerAsService()
    {
        $processor = self::getContainer()->get('orocrm_magento.provider.initial_schedule_processor');

        self::assertInstanceOf(InitialScheduleProcessor::class, $processor);
    }
}
