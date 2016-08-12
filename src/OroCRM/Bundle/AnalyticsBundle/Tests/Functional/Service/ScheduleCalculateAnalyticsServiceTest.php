<?php
namespace OroCRM\Bundle\AnalyticsBundle\Tests\Functional\Service;

use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use OroCRM\Bundle\AnalyticsBundle\Service\ScheduleCalculateAnalyticsService;

/**
 * @dbIsolationPerTest
 */
class ScheduleCalculateAnalyticsServiceTest extends WebTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->initClient();
    }

    public function testCouldBeGetFromContainerAsService()
    {
        $service = $this->getContainer()->get('orocrm_analytics.schedule_calculate_analytics');

        $this->assertInstanceOf(ScheduleCalculateAnalyticsService::class, $service);
    }
}
