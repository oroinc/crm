<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Async\Topic;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ChannelBundle\Async\Topic\LifetimeHistoryStatusUpdateTopic;
use Oro\Bundle\ChannelBundle\Entity\LifetimeValueHistory;
use Oro\Component\MessageQueue\Test\AbstractTopicTestCase;
use Oro\Component\MessageQueue\Topic\TopicInterface;
use Oro\Component\Testing\ReflectionUtil;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;

class LifetimeHistoryStatusUpdateTopicTest extends AbstractTopicTestCase
{
    protected function getTopic(): TopicInterface
    {
        return new LifetimeHistoryStatusUpdateTopic();
    }

    public function validBodyDataProvider(): array
    {
        $records = [
            [1, null, 2],
            [4, 5, $this->createAccount(6)],
            [8, null, 9],
        ];
        $expectedRecords = [
            [1, null, 2],
            [4, 5, $this->createAccount(6)],
            [8, null, 9],
        ];

        return [
            'only records' => [
                'body' => [
                    'records' => $records
                ],
                'expectedBody' => [
                    'records' => $expectedRecords,
                    'status' => LifetimeValueHistory::STATUS_OLD,
                ]
            ],
            'records and status' => [
                'body' => [
                    'records' => $records,
                    'status' => LifetimeValueHistory::STATUS_NEW,
                ],
                'expectedBody' => [
                    'records' => $expectedRecords,
                    'status' => LifetimeValueHistory::STATUS_NEW,
                ]
            ]
        ];
    }

    public function invalidBodyDataProvider(): array
    {
        return [
            'invalid records type' => [
                'body' => [
                    'records' => 'string_value',
                ],
                'exceptionClass' => InvalidOptionsException::class,
                'exceptionMessage' => '/The option "records" with value "string_value"'.
                    ' is expected to be of type "array", but is of type "string"./',
            ],
            'invalid status type' => [
                'body' => [
                    'status' => 'invalid',
                ],
                'exceptionClass' => InvalidOptionsException::class,
                'exceptionMessage' => '/The option "status" with value "invalid"'.
                    ' is expected to be of type "int"/',
            ],
            'invalid status value' => [
                'body' => [
                    'status' => 4,
                ],
                'exceptionClass' => InvalidOptionsException::class,
                'exceptionMessage' => '/The option "status" with value 4 is invalid. Accepted values are: 0, 1./',
            ]
        ];
    }

    private function createAccount($id): Account
    {
        $account = new Account();
        ReflectionUtil::setId($account, $id);
        return $account;
    }
}
