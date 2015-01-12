<?php

namespace Oro\Bundle\EmailBundle\Tests\Unit\Validator;

use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\ReminderBundle\Entity\Reminder;

use OroCRM\Bundle\TaskBundle\Validator\DueDateRequiredValidator;
use OroCRM\Bundle\TaskBundle\Validator\Constraints\DueDateRequired;
use OroCRM\Bundle\TaskBundle\Entity\Task;

class DueDateRequiredValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DueDateRequiredValidator
     */
    protected $validator;

    /**
     * @var DueDateRequired
     */
    protected $constraint;

    protected function setUp()
    {
        $this->validator  = new DueDateRequiredValidator();
        $this->constraint = $this->getMock('OroCRM\Bundle\TaskBundle\Validator\Constraints\DueDateRequired');
    }

    /**
     * @dataProvider invalidArgumentProvider
     */
    public function testInvalidArgument($value, $expectedExceptionMessage)
    {
        $this->setExpectedException(
            '\InvalidArgumentException',
            $expectedExceptionMessage
        );
        $this->validator->validate($value, $this->constraint);
    }

    public function invalidArgumentProvider()
    {
        return [
            'bool'    => [
                'value'                    => true,
                'expectedExceptionMessage' =>
                    'OroCRM\Bundle\TaskBundle\Entity\Task supported only, boolean given'
            ],
            'string'  => [
                'value'                    => 'string',
                'expectedExceptionMessage' =>
                    'OroCRM\Bundle\TaskBundle\Entity\Task supported only, string given'
            ],
            'integer' => [
                'value'                    => 5,
                'expectedExceptionMessage' =>
                    'OroCRM\Bundle\TaskBundle\Entity\Task supported only, integer given'
            ],
            'null'    => [
                'value'                    => null,
                'expectedExceptionMessage' =>
                    'OroCRM\Bundle\TaskBundle\Entity\Task supported only, NULL given'
            ],
            'object'  => [
                'value'                    => new \stdClass(),
                'expectedExceptionMessage' =>
                    'OroCRM\Bundle\TaskBundle\Entity\Task supported only, stdClass given'
            ],
            'array'   => [
                'value'                    => [],
                'expectedExceptionMessage' =>
                    'OroCRM\Bundle\TaskBundle\Entity\Task supported only, array given'
            ],
        ];
    }

    /**
     * @dataProvider validArgumentProvider
     */
    public function testValidate($entity, $addViolation)
    {
        $context = $this->getMockBuilder('Symfony\Component\Validator\ExecutionContext')
            ->disableOriginalConstructor()
            ->getMock();

        $context->expects($this->$addViolation())
            ->method('addViolationAt');

        $this->validator->initialize($context);

        $this->validator->validate($entity, $this->constraint);
    }

    public function validArgumentProvider()
    {
        return [
            'setDateAndReminders'     => [
                'entityData'   => $this->createTask(new \DateTime(), 2),
                'addViolation' => 'never',
            ],
            'setDateNoReminders'     => [
                'entityData'   => $this->createTask(new \DateTime(), 0),
                'addViolation' => 'never',
            ],
            'noDateWithReminders' => [
                'entityData'   => $this->createTask(null, 1),
                'addViolation' => 'once',
            ],
            'noDateWithoutReminders' => [
                'entityData'   => $this->createTask(null, 0),
                'addViolation' => 'never',
            ],
        ];
    }

    /**
     * @param \DateTime $dueDate
     * @param int       $remindersCount
     * @return Task
     */
    private function createTask($dueDate, $remindersCount)
    {
        $task = new Task();
        $task->setSubject(uniqid('subject'));
        $task->setDueDate($dueDate);
        $reminders = new ArrayCollection();
        for ($i = 0; $i < $remindersCount; $i++) {
            $reminders->add(new Reminder());
        }
        $task->setReminders($reminders);

        return $task;
    }
}
