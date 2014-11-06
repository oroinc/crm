<?php

namespace OroCRM\Bundle\TaskBundle\Tests\Unit\Provider;

use OroCRM\Bundle\TaskBundle\Provider\TaskCalendarNormalizer;

class TaskCalendarNormalizerTest extends \PHPUnit_Framework_TestCase
{
    /** @var TaskCalendarNormalizer */
    protected $normalizer;

    protected function setUp()
    {
        $this->normalizer = new TaskCalendarNormalizer();
    }

    /**
     * @dataProvider getTasksProvider
     */
    public function testGetTasks($tasks, $expected)
    {
        $userId = 1;
        $qb = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $query      = $this->getMockBuilder('Doctrine\ORM\AbstractQuery')
            ->disableOriginalConstructor()
            ->setMethods(['getArrayResult'])
            ->getMockForAbstractClass();
        $qb->expects($this->once())
            ->method('getQuery')
            ->will($this->returnValue($query));
        $query->expects($this->once())
            ->method('getArrayResult')
            ->will($this->returnValue($tasks));

        $result = $this->normalizer->getTasks($userId, $qb);
        $this->assertEquals($expected, $result);
    }

    public function getTasksProvider()
    {
        $createdDate = new \DateTime();
        $updatedDate = $createdDate->add(new \DateInterval('PT10S'));
        $startDate   = $createdDate->add(new \DateInterval('PT1H'));
        $end = clone($startDate);
        $endDate     = $end->add(new \DateInterval('PT30M'));

        return [
            [
                'tasks' => [
                    [
                        'id'            => 1,
                        'subject'       => 'test_subject',
                        'description'   => 'test_description',
                        'dueDate'       => $startDate,
                        'createdAt'     => $createdDate,
                        'updatedAt'     => $updatedDate,
                    ]
                ],
                'expected' => [
                    [
                        'calendar'      => 1,
                        'id'            => 'task_1',
                        'title'         => 'test_subject',
                        'description'   => 'test_description',
                        'start'         => $startDate->format('c'),
                        'end'           => $endDate->format('c'),
                        'allDay'        => false,
                        'createdAt'     => $createdDate->format('c'),
                        'updatedAt'     => $updatedDate->format('c'),
                        'editable'      => true,
                        'removable'     => true,
                        'reminders'     => [],
                    ]
                ],
            ],
        ];
    }
}
