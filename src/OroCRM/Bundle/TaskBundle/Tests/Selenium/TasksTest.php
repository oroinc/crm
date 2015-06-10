<?php

namespace OroCRM\Bundle\TaskBundle\Tests\Selenium;

use Oro\Bundle\TestFrameworkBundle\Test\Selenium2TestCase;
use OroCRM\Bundle\TaskBundle\Tests\Selenium\Pages\Tasks;

/**
 * Class CreateTaskTest
 *
 * @package OroCRM\Bundle\TaskBundle\Tests\Selenium
 */
class TasksTest extends Selenium2TestCase
{
    /**
     * @return string
     */
    public function testCreateTask()
    {
        $subject = 'Tasks_' . mt_rand();

        // set DueDate = now + 1 day to prevent "Due date must not be in the past" error
        $dueDate = new \DateTime('now');
        $dueDateValue = $dueDate
            ->add(new \DateInterval('P1D'))
            ->format('M j, Y g:i A');

        $login = $this->login();
        /** @var Tasks $login */
        $login->openTasks('OroCRM\Bundle\TaskBundle')
            ->assertTitle('All - Tasks - Activities')
            ->add()
            ->assertTitle('Create Task - Tasks - Activities')
            ->setSubject($subject)
            ->setDescription($subject)
            ->setDueDate($dueDateValue)
            ->save()
            // ->assertMessage('Task saved') // comment component using ajax and message could disappear already
            ->assertTitle("{$subject} - Tasks - Activities")
            ->toGrid()
            ->assertTitle('All - Tasks - Activities');

        return $subject;
    }

    /**
     * @depends testCreateTask
     * @param $subject
     * @return string
     */
    public function testUpdateTask($subject)
    {
        $newSubject = 'Update_' . $subject;

        $login = $this->login();
        /** @var Tasks $login */
        $login->openTasks('OroCRM\Bundle\TaskBundle')
            ->filterBy('Subject', $subject)
            ->open(array($subject))
            ->assertTitle("{$subject} - Tasks - Activities")
            ->edit()
            ->assertTitle("{$subject} - Edit - Tasks - Activities")
            ->setSubject($newSubject)
            ->save()
            // ->assertMessage('Task saved') // comment component using ajax and message could disappear already
            ->assertTitle("{$newSubject} - Tasks - Activities")
            ->toGrid()
            ->assertTitle('All - Tasks - Activities')
            ->close();

        return $newSubject;
    }

    /**
     * @depends testUpdateTask
     * @param $subject
     */
    public function testWorkflow($subject)
    {
        $login = $this->login();
        /** @var Tasks $login */
        $login->openTasks('OroCRM\Bundle\TaskBundle')
            ->filterBy('Subject', $subject)
            ->open(array($subject))
            ->process(array('Start progress' => 'In progress', 'Close' => null, 'Reopen' => null))
            ->process(array('Start progress' => null, 'Stop progress' => null, 'Close' => null));
    }

    /**
     * @depends testUpdateTask
     * @param $subject
     */
    public function testDeleteTask($subject)
    {
        $login = $this->login();
        /** @var Tasks $login */
        $login->openTasks('OroCRM\Bundle\TaskBundle')
            ->filterBy('Subject', $subject)
            ->open(array($subject))
            ->assertTitle("{$subject} - Tasks - Activities")
            ->delete()
            ->assertMessage('Task deleted')
            ->assertTitle('All - Tasks - Activities')
            ->assertNoDataMessage('No records found');
    }
}
