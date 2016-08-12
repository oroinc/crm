<?php
namespace OroCRM\Bundle\AnalyticsBundle\Tests\Unit\Async;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Test\JobRunner;
use Oro\Component\Testing\ClassExtensionTrait;
use OroCRM\Bundle\AnalyticsBundle\Async\CalculateAllChannelsAnalyticsProcessor;
use OroCRM\Bundle\AnalyticsBundle\Async\Topics;
use OroCRM\Bundle\AnalyticsBundle\Service\ScheduleCalculateAnalyticsService;

class CalculateAllChannelsAnalyticsProcessorTest extends \PHPUnit_Framework_TestCase
{
    use ClassExtensionTrait;

    public function testShouldImplementMessageProcessorInterface()
    {
        $this->assertClassImplements(MessageProcessorInterface::class, CalculateAllChannelsAnalyticsProcessor::class);
    }

    public function testShouldImplementTopicSubscriberInterface()
    {
        $this->assertClassImplements(TopicSubscriberInterface::class, CalculateAllChannelsAnalyticsProcessor::class);
    }

    public function testShouldSubscribeOnCalculateAllChannelsAnalyticsTopic()
    {
        $this->assertEquals(
            [Topics::CALCULATE_ALL_CHANNELS_ANALYTICS],
            CalculateAllChannelsAnalyticsProcessor::getSubscribedTopics()
        );
    }

    public function testCouldBeConstructedWithExpectedArguments()
    {
        new CalculateAllChannelsAnalyticsProcessor(
            $this->createDoctrineHelperMock(),
            $this->createScheduleCalculateAnalyticsServiceMock(),
            new JobRunner()
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ScheduleCalculateAnalyticsService
     */
    private function createScheduleCalculateAnalyticsServiceMock()
    {
        return $this->getMock(ScheduleCalculateAnalyticsService::class, [], [], '', false);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|DoctrineHelper
     */
    private function createDoctrineHelperMock()
    {
        return $this->getMock(DoctrineHelper::class, [], [], '', false);
    }
}
