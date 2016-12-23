<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\Entity;

use Doctrine\Common\Util\ClassUtils;

use Oro\Bundle\ChannelBundle\Entity\LifetimeValueHistory;

class LifetimeValueHistoryTest extends AbstractEntityTestCase
{
    /** @var LifetimeValueHistory */
    protected $entity;

    /**
     * {@inheritDoc}
     */
    public function getEntityFQCN()
    {
        return 'Oro\Bundle\ChannelBundle\Entity\LifetimeValueHistory';
    }

    /**
     * {@inheritDoc}
     */
    public function getDataProvider()
    {
        $someDateTime = new \DateTime();
        $someAmount   = 123.12;
        $account      = $this->createMock('Oro\Bundle\AccountBundle\Entity\Account');
        $channel      = $this->createMock('Oro\Bundle\ChannelBundle\Entity\Channel');
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
        $this->assertNull($this->entity->getId());

        $testId = 22;
        $ref    = new \ReflectionProperty(ClassUtils::getClass($this->entity), 'id');
        $ref->setAccessible(true);
        $ref->setValue($this->entity, $testId);

        $this->assertAttributeSame($testId, 'id', $this->entity);
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
