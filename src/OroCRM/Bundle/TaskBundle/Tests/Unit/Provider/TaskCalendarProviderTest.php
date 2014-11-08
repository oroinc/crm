<?php

namespace OroCRM\Bundle\TaskBundle\Tests\Unit\Provider;

use Doctrine\ORM\Query\Expr;

use Oro\Bundle\CalendarBundle\Entity\CalendarProperty;
use OroCRM\Bundle\TaskBundle\Provider\TaskCalendarProvider;

class TaskCalendarProviderTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $doctrineHelper;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $taskCalendarNormalizer;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $translator;

    /** @var bool */
    protected $enabled = true;

    /** @var TaskCalendarProvider */
    protected $provider;

    protected function setUp()
    {
        $this->doctrineHelper         = $this->getMockBuilder('Oro\Bundle\EntityBundle\ORM\DoctrineHelper')
            ->disableOriginalConstructor()
            ->getMock();
        $this->taskCalendarNormalizer =
            $this->getMockBuilder('OroCRM\Bundle\TaskBundle\Provider\TaskCalendarNormalizer')
                ->disableOriginalConstructor()
                ->getMock();
        $this->translator             = $this->getMock('Symfony\Component\Translation\TranslatorInterface');

        $this->provider = new TaskCalendarProvider(
            $this->doctrineHelper,
            $this->taskCalendarNormalizer,
            $this->translator,
            $this->enabled
        );
    }

    public function testGetCalendarDefaultValues()
    {
        $userId     = 123;
        $calendarId = 456;

        $this->translator->expects($this->exactly(2))
            ->method('trans')
            ->will($this->returnArgument(0));

        $this->assertEquals(
            [
                TaskCalendarProvider::MY_TASKS_CALENDAR_ID => [
                    'calendarName'    => 'orocrm.task.menu.my_tasks',
                    'removable'       => false,
                    'position'        => -1,
                    'backgroundColor' => 'F83A22',
                    'widgetRoute'     => 'orocrm_task_widget_info',
                    'widgetOptions'   => [
                        'title'         => 'orocrm.task.info_widget_title',
                        'dialogOptions' => [
                            'width' => 600
                        ]
                    ]
                ]
            ],
            $this->provider->getCalendarDefaultValues($userId, $calendarId, [])
        );
    }

    public function testGetCalendarName()
    {
        $connection = new CalendarProperty();
        $connection->setCalendar(TaskCalendarProvider::MY_TASKS_CALENDAR_ID);

        $this->translator->expects($this->once())
            ->method('trans')
            ->will($this->returnArgument(0));

        $this->assertEquals(
            'orocrm.task.menu.my_tasks',
            $this->provider->getCalendarName($connection)
        );
    }

    /**
     * @dataProvider getCalendarEventsProvider
     */
    public function testGetCalendarEvents($connections, $tasks)
    {
        $userId      = 123;
        $calendarId  = 456;
        $start       = new \DateTime();
        $end         = new \DateTime();
        $subordinate = true;

        $connectionQuery = $this->getMockBuilder('Doctrine\ORM\AbstractQuery')
            ->disableOriginalConstructor()
            ->setMethods(['getArrayResult'])
            ->getMockForAbstractClass();
        $connectionQb    = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $connectionRepo  = $this
            ->getMockBuilder('Oro\Bundle\CalendarBundle\Entity\Repository\CalendarPropertyRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $connectionRepo->expects($this->once())
            ->method('getConnectionsByTargetCalendarQueryBuilder')
            ->with($calendarId, TaskCalendarProvider::ALIAS)
            ->will($this->returnValue($connectionQb));
        $connectionQb->expects($this->once())
            ->method('select')
            ->with('connection.calendar, connection.visible')
            ->will($this->returnSelf());
        $connectionQb->expects($this->once())
            ->method('getQuery')
            ->will($this->returnValue($connectionQuery));
        $connectionQuery->expects($this->once())
            ->method('getArrayResult')
            ->will($this->returnValue($connections));

        $qb   = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $repo = $this->getMockBuilder('OroCRM\Bundle\TaskBundle\Entity\Repository\TaskRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $repo->expects($this->once())
            ->method('getTaskListByTimeIntervalQueryBuilder')
            ->with($userId, $this->identicalTo($start), $this->identicalTo($end))
            ->will($this->returnValue($qb));

        $this->doctrineHelper->expects($this->exactly(2))
            ->method('getEntityRepository')
            ->will(
                $this->returnValueMap(
                    [
                        ['OroCalendarBundle:CalendarProperty', $connectionRepo],
                        ['OroCRMTaskBundle:Task', $repo],
                    ]
                )
            );

        $this->taskCalendarNormalizer->expects($this->once())
            ->method('getTasks')
            ->with(TaskCalendarProvider::MY_TASKS_CALENDAR_ID, $this->identicalTo($qb))
            ->will($this->returnValue($tasks));

        $result = $this->provider->getCalendarEvents($userId, $calendarId, $start, $end, $subordinate);
        $this->assertEquals($tasks, $result);
    }

    public function getCalendarEventsProvider()
    {
        return [
            'no connections' => [
                'connections' => [],
                'tasks'       => [['id' => 1]]
            ],
            'with visible connection' => [
                'connections' => [
                    ['calendar' => TaskCalendarProvider::MY_TASKS_CALENDAR_ID, 'visible' => true]
                ],
                'tasks'       => [['id' => 1]]
            ],
        ];
    }

    public function testGetCalendarEventsWithInvisibleConnection()
    {
        $userId      = 123;
        $calendarId  = 456;
        $start       = new \DateTime();
        $end         = new \DateTime();
        $subordinate = true;
        $connections = [['calendar' => TaskCalendarProvider::MY_TASKS_CALENDAR_ID, 'visible' => false]];

        $connectionQuery = $this->getMockBuilder('Doctrine\ORM\AbstractQuery')
            ->disableOriginalConstructor()
            ->setMethods(['getArrayResult'])
            ->getMockForAbstractClass();
        $connectionQb    = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $connectionRepo  = $this
            ->getMockBuilder('Oro\Bundle\CalendarBundle\Entity\Repository\CalendarPropertyRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $connectionRepo->expects($this->once())
            ->method('getConnectionsByTargetCalendarQueryBuilder')
            ->with($calendarId, TaskCalendarProvider::ALIAS)
            ->will($this->returnValue($connectionQb));
        $connectionQb->expects($this->once())
            ->method('select')
            ->with('connection.calendar, connection.visible')
            ->will($this->returnSelf());
        $connectionQb->expects($this->once())
            ->method('getQuery')
            ->will($this->returnValue($connectionQuery));
        $connectionQuery->expects($this->once())
            ->method('getArrayResult')
            ->will($this->returnValue($connections));

        $this->doctrineHelper->expects($this->once())
            ->method('getEntityRepository')
            ->with('OroCalendarBundle:CalendarProperty')
            ->will($this->returnValue($connectionRepo));

        $this->taskCalendarNormalizer->expects($this->never())
            ->method('getTasks');

        $result = $this->provider->getCalendarEvents($userId, $calendarId, $start, $end, $subordinate);
        $this->assertEquals([], $result);
    }
}
