<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Entity;

use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Entity\LifetimeValueHistory;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;
use PHPUnit\Framework\TestCase;

class LifetimeValueHistoryTest extends TestCase
{
    use EntityTestCaseTrait;

    public function testProperties(): void
    {
        $properties = [
            'id'          => ['id', 1],
            'status'      => ['status', true],
            'amount'      => ['amount', 123.12],
            'account'     => ['account', $this->createMock(Account::class)],
            'dataChannel' => ['dataChannel', $this->createMock(Channel::class)],
            'createdAt'   => ['createdAt', new \DateTime()],
        ];

        $entity = new LifetimeValueHistory();
        self::assertPropertyAccessors($entity, $properties);
    }

    public function testPrePersist(): void
    {
        $entity = new LifetimeValueHistory();
        $entity->prePersist();

        self::assertNotNull($entity->getCreatedAt());

        $existingCreatedAt = $entity->getCreatedAt();
        $entity->prePersist();
        self::assertSame($existingCreatedAt, $entity->getCreatedAt());
    }
}
