<?php
namespace Oro\Bundle\ChannelBundle\Tests\Unit\Async;

use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Transport\Null\NullMessage;
use Oro\Component\MessageQueue\Transport\Null\NullSession;
use Oro\Component\Testing\ClassExtensionTrait;
use Oro\Bundle\ChannelBundle\Async\ProcessChannelStateProcessor;
use Oro\Bundle\ChannelBundle\Async\Topics;
use Oro\Bundle\ChannelBundle\Provider\StateProvider;

class ProcessChannelStateProcessorTest extends \PHPUnit_Framework_TestCase
{
    use ClassExtensionTrait;

    public function testShouldImplementMessageProcessorInterface()
    {
        $this->assertClassImplements(MessageProcessorInterface::class, ProcessChannelStateProcessor::class);
    }

    public function testShouldImplementTopicSubscriberInterface()
    {
        $this->assertClassImplements(TopicSubscriberInterface::class, ProcessChannelStateProcessor::class);
    }

    public function testShouldSubscribeOnChannelStatusChangedTopic()
    {
        $this->assertEquals([Topics::CHANNEL_STATUS_CHANGED], ProcessChannelStateProcessor::getSubscribedTopics());
    }

    public function testCouldBeConstructedWithStateProviderAsFirstArgument()
    {
        new ProcessChannelStateProcessor($this->createStateProvider());
    }

    public function testShouldCallProcessChannelChangeOnStateProviderService()
    {
        $providerMock = $this->createStateProvider();
        $providerMock
            ->expects($this->once())
            ->method('processChannelChange')
        ;

        $processor = new ProcessChannelStateProcessor($providerMock);

        $processor->process(new NullMessage(), new NullSession());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|StateProvider
     */
    protected function createStateProvider()
    {
        return $this->createMock(StateProvider::class);
    }
}
