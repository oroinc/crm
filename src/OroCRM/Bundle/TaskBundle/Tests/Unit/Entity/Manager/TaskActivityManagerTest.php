<?php

namespace OroCRM\Bundle\TaskBundle\Tests\Unit\Entity\Manager;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;

use Oro\Bundle\UserBundle\Entity\User;
use OroCRM\Bundle\TaskBundle\Entity\Manager\TaskActivityManager;
use OroCRM\Bundle\TaskBundle\Entity\Task;

class TaskActivityManagerTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $em;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $uow;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $activityManager;

    /** @var TaskActivityManager */
    protected $manager;

    protected function setUp()
    {
        $this->em  = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->uow = $this->getMockBuilder('\Doctrine\ORM\UnitOfWork')
            ->disableOriginalConstructor()
            ->getMock();
        $this->em->expects($this->any())
            ->method('getUnitOfWork')
            ->will($this->returnValue($this->uow));

        $this->activityManager = $this->getMockBuilder('Oro\Bundle\ActivityBundle\Manager\ActivityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->manager = new TaskActivityManager($this->activityManager);
    }

    /**
     * Test new task creation
     */
    public function testHandleOnFlushCreateTask()
    {
        $args = new OnFlushEventArgs($this->em);
        $user1 = new User();
        $user2 = new User();
        $task = new Task();
        $task->setOwner($user1);
        $task->setReporter($user2);

        $taskMetadata = new ClassMetadata(get_class($task));

        $this->em->expects($this->once())
            ->method('getUnitOfWork')
            ->will($this->returnValue($this->uow));

        $this->uow->expects($this->once())
            ->method('getScheduledEntityInsertions')
            ->will($this->returnValue([$task]));
        $this->uow->expects($this->once())
            ->method('getScheduledEntityUpdates')
            ->will($this->returnValue([]));

        $this->activityManager->expects($this->at(0))
            ->method('addActivityTarget')
            ->with($this->identicalTo($task), $this->identicalTo($user1))
            ->will($this->returnValue(true));
        $this->activityManager->expects($this->at(1))
            ->method('addActivityTarget')
            ->with($this->identicalTo($task), $this->identicalTo($user2))
            ->will($this->returnValue(true));
        $this->em->expects($this->once())
            ->method('getClassMetadata')
            ->with('OroCRM\Bundle\TaskBundle\Entity\Task')
            ->will($this->returnValue($taskMetadata));
        $this->uow->expects($this->once())
            ->method('computeChangeSet')
            ->with($this->identicalTo($taskMetadata), $this->identicalTo($task));

        $this->manager->handleOnFlush($args);
    }

    /**
     * Test new task creation when activity association already exist or disabled
     */
    public function testHandleOnFlushCreateTaskWithDisabledActivity()
    {
        $args = new OnFlushEventArgs($this->em);
        $user1 = new User();
        $user2 = new User();
        $task = new Task();
        $task->setOwner($user1);
        $task->setReporter($user2);

        $this->em->expects($this->once())
            ->method('getUnitOfWork')
            ->will($this->returnValue($this->uow));

        $this->uow->expects($this->once())
            ->method('getScheduledEntityInsertions')
            ->will($this->returnValue([$task]));
        $this->uow->expects($this->once())
            ->method('getScheduledEntityUpdates')
            ->will($this->returnValue([]));

        $this->activityManager->expects($this->at(0))
            ->method('addActivityTarget')
            ->with($this->identicalTo($task), $this->identicalTo($user1))
            ->will($this->returnValue(false));
        $this->activityManager->expects($this->at(1))
            ->method('addActivityTarget')
            ->with($this->identicalTo($task), $this->identicalTo($user2))
            ->will($this->returnValue(false));
        $this->em->expects($this->never())
            ->method('getClassMetadata');
        $this->uow->expects($this->never())
            ->method('computeChangeSet');

        $this->manager->handleOnFlush($args);
    }

    /**
     * Test update existing task
     */
    public function testHandleOnFlushUpdateTask()
    {
        $args  = new OnFlushEventArgs($this->em);
        $user1 = new User();
        $user2 = new User();
        $user3 = new User();
        $user4 = new User();
        $task = new Task();
        $task->setOwner($user3);
        $task->setReporter($user4);

        $taskMetadata = new ClassMetadata(get_class($task));

        $this->em->expects($this->once())
            ->method('getUnitOfWork')
            ->will($this->returnValue($this->uow));

        $this->uow->expects($this->once())
            ->method('getScheduledEntityInsertions')
            ->will($this->returnValue([]));
        $this->uow->expects($this->once())
            ->method('getScheduledEntityUpdates')
            ->will($this->returnValue([$task]));

        $this->uow->expects($this->once())
            ->method('getEntityChangeSet')
            ->with($this->identicalTo($task))
            ->will($this->returnValue(['owner' => [$user1, $user3], 'reporter' => [$user2, $user4]]));

        $this->activityManager->expects($this->at(0))
            ->method('removeActivityTarget')
            ->with($this->identicalTo($task), $this->identicalTo($user1))
            ->will($this->returnValue(true));
        $this->activityManager->expects($this->at(1))
            ->method('removeActivityTarget')
            ->with($this->identicalTo($task), $this->identicalTo($user2))
            ->will($this->returnValue(true));
        $this->activityManager->expects($this->at(2))
            ->method('addActivityTarget')
            ->with($this->identicalTo($task), $this->identicalTo($user3))
            ->will($this->returnValue(true));
        $this->activityManager->expects($this->at(3))
            ->method('addActivityTarget')
            ->with($this->identicalTo($task), $this->identicalTo($user4))
            ->will($this->returnValue(true));
        $this->em->expects($this->once())
            ->method('getClassMetadata')
            ->with('OroCRM\Bundle\TaskBundle\Entity\Task')
            ->will($this->returnValue($taskMetadata));
        $this->uow->expects($this->once())
            ->method('computeChangeSet')
            ->with($this->identicalTo($taskMetadata), $this->identicalTo($task));

        $this->manager->handleOnFlush($args);
    }

    /**
     * Test update existing task when old owner and reporter are the same user
     */
    public function testHandleOnFlushUpdateTaskWithSameOwnerAndReporter()
    {
        $args  = new OnFlushEventArgs($this->em);
        $user1 = new User();
        $user2 = new User();
        $task = new Task();
        $task->setOwner($user1);
        $task->setReporter($user2);

        $taskMetadata = new ClassMetadata(get_class($task));

        $this->em->expects($this->once())
            ->method('getUnitOfWork')
            ->will($this->returnValue($this->uow));

        $this->uow->expects($this->once())
            ->method('getScheduledEntityInsertions')
            ->will($this->returnValue([]));
        $this->uow->expects($this->once())
            ->method('getScheduledEntityUpdates')
            ->will($this->returnValue([$task]));

        $this->uow->expects($this->once())
            ->method('getEntityChangeSet')
            ->with($this->identicalTo($task))
            ->will($this->returnValue(['owner' => [$user1, $user2]]));

        $this->activityManager->expects($this->at(0))
            ->method('addActivityTarget')
            ->with($this->identicalTo($task), $this->identicalTo($user2))
            ->will($this->returnValue(true));
        $this->em->expects($this->once())
            ->method('getClassMetadata')
            ->with('OroCRM\Bundle\TaskBundle\Entity\Task')
            ->will($this->returnValue($taskMetadata));
        $this->uow->expects($this->once())
            ->method('computeChangeSet')
            ->with($this->identicalTo($taskMetadata), $this->identicalTo($task));

        $this->manager->handleOnFlush($args);
    }

    /**
     * Test update existing task when activity association already exist or disabled
     */
    public function testHandleOnFlushUpdateTaskWithDisabledActivity()
    {
        $args  = new OnFlushEventArgs($this->em);
        $user1 = new User();
        $user2 = new User();
        $user3 = new User();
        $user4 = new User();
        $task = new Task();
        $task->setOwner($user3);
        $task->setReporter($user4);

        $this->em->expects($this->once())
            ->method('getUnitOfWork')
            ->will($this->returnValue($this->uow));

        $this->uow->expects($this->once())
            ->method('getScheduledEntityInsertions')
            ->will($this->returnValue([]));
        $this->uow->expects($this->once())
            ->method('getScheduledEntityUpdates')
            ->will($this->returnValue([$task]));

        $this->uow->expects($this->once())
            ->method('getEntityChangeSet')
            ->with($this->identicalTo($task))
            ->will($this->returnValue(['owner' => [$user1, $user3], 'reporter' => [$user2, $user4]]));

        $this->activityManager->expects($this->at(0))
            ->method('removeActivityTarget')
            ->with($this->identicalTo($task), $this->identicalTo($user1))
            ->will($this->returnValue(false));
        $this->activityManager->expects($this->at(1))
            ->method('removeActivityTarget')
            ->with($this->identicalTo($task), $this->identicalTo($user2))
            ->will($this->returnValue(false));
        $this->activityManager->expects($this->at(2))
            ->method('addActivityTarget')
            ->with($this->identicalTo($task), $this->identicalTo($user3))
            ->will($this->returnValue(false));
        $this->activityManager->expects($this->at(3))
            ->method('addActivityTarget')
            ->with($this->identicalTo($task), $this->identicalTo($user4))
            ->will($this->returnValue(false));
        $this->em->expects($this->never())
            ->method('getClassMetadata');
        $this->uow->expects($this->never())
            ->method('computeChangeSet');

        $this->manager->handleOnFlush($args);
    }
}
