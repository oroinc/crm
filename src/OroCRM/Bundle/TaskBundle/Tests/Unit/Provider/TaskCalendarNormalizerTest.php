<?php

namespace OroCRM\Bundle\TaskBundle\Tests\Unit\Provider;

use OroCRM\Bundle\TaskBundle\Provider\TaskCalendarNormalizer;

class TaskCalendarNormalizerTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $reminderManager;

    /** @var TaskCalendarNormalizer */
    protected $normalizer;

    protected function setUp()
    {
        $this->reminderManager = $this->getMockBuilder('Oro\Bundle\ReminderBundle\Entity\Manager\ReminderManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->normalizer = new TaskCalendarNormalizer($this->reminderManager);
    }

    /**
     * @dataProvider getTasksProvider
     */
    public function testGetTasks($tasks, $expected)
    {
        $calendarId = 123;

        $query = $this->getMockBuilder('Doctrine\ORM\AbstractQuery')
            ->disableOriginalConstructor()
            ->setMethods(['getArrayResult'])
            ->getMockForAbstractClass();
        $query->expects($this->once())
            ->method('getArrayResult')
            ->will($this->returnValue($tasks));

        $this->reminderManager->expects($this->once())
            ->method('applyReminders')
            ->with($expected, 'OroCRM\Bundle\TaskBundle\Entity\Task');

        $result = $this->normalizer->getTasks($calendarId, $query);
        $this->assertEquals($expected, $result);
    }

    public function getTasksProvider()
    {
        $createdDate = new \DateTime();
        $updatedDate = $createdDate->add(new \DateInterval('PT10S'));
        $startDate   = $createdDate->add(new \DateInterval('PT1H'));
        $end         = clone($startDate);
        $endDate     = $end->add(new \DateInterval('PT30M'));

        return [
            [
                'tasks'    => [
                    [
                        'id'          => 1,
                        'subject'     => 'test_subject',
                        'description' => 'test_description',
                        'dueDate'     => $startDate,
                        'createdAt'   => $createdDate,
                        'updatedAt'   => $updatedDate,
                    ]
                ],
                'expected' => [
                    [
                        'calendar'    => 123,
                        'id'          => 1,
                        'title'       => 'test_subject',
                        'description' => 'test_description',
                        'start'       => $startDate->format('c'),
                        'end'         => $endDate->format('c'),
                        'allDay'      => false,
                        'createdAt'   => $createdDate->format('c'),
                        'updatedAt'   => $updatedDate->format('c'),
                        'editable'    => false,
                        'removable'   => false,
                    ]
                ],
            ],
        ];
    }
}
