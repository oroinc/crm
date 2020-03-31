<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Async;

use Oro\Bundle\ChannelBundle\Async\ProcessChannelStateProcessor;
use Oro\Bundle\ChannelBundle\Async\Topics;
use Oro\Bundle\ChannelBundle\Provider\StateProvider;
use Oro\Component\MessageQueue\Client\TopicSubscriberInterface;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Transport\Message;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Oro\Component\Testing\ClassExtensionTrait;

class ProcessChannelStateProcessorTest extends \PHPUnit\Framework\TestCase
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

        /** @var SessionInterface|\PHPUnit\Framework\MockObject\MockObject $session */
        $session = $this->createMock(SessionInterface::class);
        $processor->process(new Message(), new $session);
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|StateProvider
     */
    protected function createStateProvider()
    {
        return $this->createMock(StateProvider::class);
    }
}
