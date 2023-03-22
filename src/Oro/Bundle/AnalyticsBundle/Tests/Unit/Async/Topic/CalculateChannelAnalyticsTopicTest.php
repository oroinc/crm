<?php

namespace Oro\Bundle\AnalyticsBundle\Tests\Unit\Async\Topic;

use Oro\Bundle\AnalyticsBundle\Async\Topic\CalculateChannelAnalyticsTopic;
use Oro\Component\MessageQueue\Client\MessagePriority;
use Oro\Component\MessageQueue\Test\AbstractTopicTestCase;
use Oro\Component\MessageQueue\Topic\TopicInterface;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

class CalculateChannelAnalyticsTopicTest extends AbstractTopicTestCase
{
    protected function getTopic(): TopicInterface
    {
        return new CalculateChannelAnalyticsTopic();
    }

    public function validBodyDataProvider(): array
    {
        $requiredOptionsSet = [
            'channel_id' => 1,
        ];
        $fullOptionsSet = [
            'channel_id' => 1,
            'customer_ids' => [1, 2, 3],
        ];

        return [
            'only required options' => [
                'body' => $requiredOptionsSet,
                'expectedBody' => array_merge(
                    $requiredOptionsSet,
                    [
                        'customer_ids' => [],
                    ]
                ),
            ],
            'full set of options' => [
                'body' => $fullOptionsSet,
                'expectedBody' => $fullOptionsSet,
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
                    '/The required option "channel_id" is missing./',
            ],
            'wrong channel_id type' => [
                'body' => [
                    'channel_id' => '1',
                ],
                'exceptionClass' => InvalidOptionsException::class,
                'exceptionMessage' => '/The option "channel_id" with value "1" is expected to be of type "int"/',
            ],
            'wrong customer_ids type' => [
                'body' => [
                    'channel_id' => 1,
                    'customer_ids' => 1,
                ],
                'exceptionClass' => InvalidOptionsException::class,
                'exceptionMessage' => '/The option "customer_ids" with value 1 is expected to be of type "int\[\]"/',
            ],
            'wrong customer_ids array values type' => [
                'body' => [
                    'channel_id' => 1,
                    'customer_ids' => [
                        '1',
                    ],
                ],
                'exceptionClass' => InvalidOptionsException::class,
                'exceptionMessage' => '/The option "customer_ids" with value array is expected to be'
                    . ' of type "int\[\]", but one of the elements is of type "string"./',
            ],
        ];
    }

    public function testPriority(): void
    {
        self::assertEquals(MessagePriority::VERY_LOW, $this->getTopic()->getDefaultPriority('queueName'));
    }

    public function testCreateJobName(): void
    {
        self::assertSame(
            'oro_analytics:calculate_channel_analytics:42',
            $this->getTopic()->createJobName(['channel_id' => 42])
        );
    }
}
