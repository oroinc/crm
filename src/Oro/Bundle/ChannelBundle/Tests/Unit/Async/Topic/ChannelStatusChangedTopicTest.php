<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Async\Topic;

use Oro\Bundle\ChannelBundle\Async\Topic\ChannelStatusChangedTopic;
use Oro\Component\MessageQueue\Test\AbstractTopicTestCase;
use Oro\Component\MessageQueue\Topic\TopicInterface;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class ChannelStatusChangedTopicTest extends AbstractTopicTestCase
{
    protected function getTopic(): TopicInterface
    {
        return new ChannelStatusChangedTopic();
    }

    public function validBodyDataProvider(): array
    {
        $requiredOptionsSet = [
            'channelId' => 1,
        ];

        return [
            'only required options' => [
                'body' => $requiredOptionsSet,
                'expectedBody' => $requiredOptionsSet,
            ],
        ];
    }

    public function invalidBodyDataProvider(): array
    {
        return [
            'empty' => [
                'body' => [],
                'exceptionClass' => MissingOptionsException::class,
                'exceptionMessage' =>
                    '/The required option "channelId" is missing./',
            ],
            'wrong channelId type' => [
                'body' => [
                    'channelId' => '1',
                ],
                'exceptionClass' => InvalidOptionsException::class,
                'exceptionMessage' => '/The option "channelId" with value "1" is expected to be of type "int"/',
            ],
        ];
    }
}
