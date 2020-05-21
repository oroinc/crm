<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Entity;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Entity\LifetimeValueHistory;

class LifetimeValueHistoryTest extends AbstractEntityTestCase
{
    /** @var LifetimeValueHistory */
    protected $entity;

    public function getEntityFQCN()
    {
        return LifetimeValueHistory::class;
    }

    public function getDataProvider()
    {
        $someDateTime = new \DateTime();
        $someAmount   = 123.12;
        $account      = $this->createMock(Account::class);
        $channel      = $this->createMock(Channel::class);
        $status       = true;

        return [
            'status'      => ['status', $status, $status],
            'amount'      => ['amount', $someAmount, $someAmount],
            'account'     => ['account', $account, $account],
            'dataChannel' => ['dataChannel', $channel, $channel],
            'createdAt'   => ['createdAt', $someDateTime, $someDateTime],
        ];
    }

    public function testGetId()
    {
        $entity = new class() extends LifetimeValueHistory {
            public function xsetId(int $id): void
            {
                $this->id = $id;
            }
        };

        static::assertNull($entity->getId());

        $testId = 2345;
        $entity->xsetId($testId);

        static::assertEquals($testId, $entity->getId());
    }

    public function testPrePersist()
    {
        $this->assertNull($this->entity->getCreatedAt());

        $this->entity->prePersist();

        $result = $this->entity->getCreatedAt();
        $this->assertInstanceOf('DateTime', $result);
        $this->assertLessThan(3, $result->diff(new \DateTime())->s);

        $this->entity->prePersist();
        $this->assertSame($result, $this->entity->getCreatedAt());
    }
}
