<?php

namespace OroCRM\Bundle\ChannelBundle\Tests\Unit\Entity;

use Doctrine\Common\Util\ClassUtils;
use OroCRM\Bundle\ChannelBundle\Entity\LifetimeValueHistory;

class LifetimeValueHistoryTest extends AbstractEntityTestCase
{
    /** @var LifetimeValueHistory */
    protected $entity;

    /**
     * {@inheritDoc}
     */
    public function getEntityFQCN()
    {
        return 'OroCRM\Bundle\ChannelBundle\Entity\LifetimeValueHistory';
    }

    /**
     * {@inheritDoc}
     */
    public function getDataProvider()
    {
        $someDateTime = new \DateTime();
        $someAmount   = 123.12;
        $account      = $this->getMock('OroCRM\Bundle\AccountBundle\Entity\Account');
        $channel      = $this->getMock('OroCRM\Bundle\ChannelBundle\Entity\Channel');

        return [
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

        $this->assertInstanceOf('DateTime', $this->entity->getCreatedAt());
        $this->assertLessThan(3, $this->entity->getCreatedAt()->diff(new \DateTime())->s);
    }
}
