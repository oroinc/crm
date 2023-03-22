<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Async\Topic;

use Oro\Bundle\ChannelBundle\Async\Topic\AggregateLifetimeAverageTopic;
use Oro\Component\MessageQueue\Client\MessagePriority;
use Oro\Component\MessageQueue\Test\AbstractTopicTestCase;
use Oro\Component\MessageQueue\Topic\TopicInterface;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

class AggregateLifetimeAverageTopicTest extends AbstractTopicTestCase
{
    protected function getTopic(): TopicInterface
    {
        return new AggregateLifetimeAverageTopic();
    }

    public function validBodyDataProvider(): array
    {
        $fullOptionsSet = [
            'force' => true,
            'use_truncate' => false,
        ];

        return [
            'only required options' => [
                'body' => [],
                'expectedBody' => [
                    'force' => false,
                    'use_truncate' => true,
                ],
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
            'wrong force type' => [
                'body' => [
                    'force' => '1',
                ],
                'exceptionClass' => InvalidOptionsException::class,
                'exceptionMessage' => '/The option "force" with value "1" is expected to be of type "bool"/',
            ],
            'wrong use_truncate type' => [
                'body' => [
                    'use_truncate' => '1',
                ],
                'exceptionClass' => InvalidOptionsException::class,
                'exceptionMessage' => '/The option "use_truncate" with value "1" is expected to be of type "bool"/',
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
            'oro_channel:aggregate_lifetime_average',
            $this->getTopic()->createJobName([])
        );
    }
}
