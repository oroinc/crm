<?php

namespace Oro\Bundle\ChannelBundle\Tests\Unit\EventListener;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\EventListener\ORM\RefreshChannelCacheListener;
use Oro\Bundle\ChannelBundle\Provider\StateProvider;
use Oro\Component\TestUtils\ORM\OrmTestCase;

class RefreshChannelCacheListenerTest extends OrmTestCase
{
    /** @var StateProvider | \PHPUnit\Framework\MockObject\MockObject */
    protected $stateProvider;

    /** @var  RefreshChannelCacheListener */
    protected $refreshChannelCacheListener;

    /** @var EntityManager|\PHPUnit\Framework\MockObject\MockObject */
    protected $em;

    protected function setUp(): void
    {
        $this->stateProvider = $this
            ->getMockBuilder(StateProvider::class)
            ->disableOriginalConstructor()->getMock();

        $this->em = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()->getMock();

        $this->refreshChannelCacheListener = new RefreshChannelCacheListener($this->stateProvider);
    }

    public function testPrePersist()
    {
        $this->stateProvider->expects($this->once())
            ->method('processChannelChange');

        $channel = new Channel();
        $eventArgs = new LifecycleEventArgs($channel, $this->em);
        $this->refreshChannelCacheListener->prePersist($channel, $eventArgs);
    }

    public function testPostRemove()
    {
        $this->stateProvider->expects($this->once())
            ->method('processChannelChange');

        $channel = new Channel();
        $eventArgs = new LifecycleEventArgs($channel, $this->em);
        $this->refreshChannelCacheListener->postRemove($channel, $eventArgs);
    }
}
