<?php

namespace OroCRM\Bundle\TaskBundle\Tests\Unit\Entity\Manager;

use OroCRM\Bundle\TaskBundle\Entity\Task;
use OroCRM\Bundle\TaskBundle\Entity\Manager\TaskActivityManager;
use OroCRM\Bundle\TaskBundle\Tests\Unit\Fixtures\Entity\TestTarget;

class TaskActivityManagerTest extends \PHPUnit_Framework_TestCase
{
    /** @var TaskActivityManager */
    private $manager;

    protected function setUp()
    {
        $this->manager = new TaskActivityManager();
    }

    public function testAddAssociation()
    {
        $task = new Task();
        $target = new TestTarget();

        $this->assertTrue(
            $this->manager->addAssociation($task, $target)
        );
    }
}
