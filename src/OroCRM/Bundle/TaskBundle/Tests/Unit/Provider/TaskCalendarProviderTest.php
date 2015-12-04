<?php

namespace OroCRM\Bundle\TaskBundle\Tests\Unit\Provider;

use Doctrine\ORM\Query\Expr;

use OroCRM\Bundle\TaskBundle\Provider\TaskCalendarProvider;

class TaskCalendarProviderTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $doctrineHelper;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $aclHelper;

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
        $this->aclHelper              = $this->getMockBuilder('Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper')
            ->disableOriginalConstructor()
            ->getMock();
        $this->taskCalendarNormalizer =
            $this->getMockBuilder('OroCRM\Bundle\TaskBundle\Provider\TaskCalendarNormalizer')
                ->disableOriginalConstructor()
                ->getMock();
        $this->translator             = $this->getMock('Symfony\Component\Translation\TranslatorInterface');

        $this->provider = new TaskCalendarProvider(
            $this->doctrineHelper,
            $this->aclHelper,
            $this->taskCalendarNormalizer,
            $this->translator,
            $this->enabled
        );
    }

    public function testGetCalendarDefaultValuesDisabled()
    {
        $organizationId = 1;
        $userId         = 123;
        $calendarId     = 10;
        $calendarIds    = [TaskCalendarProvider::MY_TASKS_CALENDAR_ID];

        $provider = new TaskCalendarProvider(
            $this->doctrineHelper,
            $this->aclHelper,
            $this->taskCalendarNormalizer,
            $this->translator,
            false
        );

        $result = $provider->getCalendarDefaultValues($organizationId, $userId, $calendarId, $calendarIds);
        $this->assertEquals(
            [
                TaskCalendarProvider::MY_TASKS_CALENDAR_ID => null
            ],
            $result
        );
    }

    public function testGetCalendarDefaultValues()
    {
        $organizationId = 1;
        $userId         = 123;
        $calendarId     = 10;

        $this->translator->expects($this->exactly(2))
            ->method('trans')
            ->will($this->returnArgument(0));

        $this->assertEquals(
            [
                TaskCalendarProvider::MY_TASKS_CALENDAR_ID => [
                    'calendarName'    => 'orocrm.task.menu.my_tasks',
                    'removable'       => false,
                    'position'        => -100,
                    'backgroundColor' => '#F83A22',
                    'options'         => [
                        'widgetRoute'   => 'orocrm_task_widget_info',
                        'widgetOptions' => [
                            'title'         => 'orocrm.task.info_widget_title',
                            'dialogOptions' => [
                                'width' => 600
                            ]
                        ]
                    ]
                ]
            ],
            $this->provider->getCalendarDefaultValues($organizationId, $userId, $calendarId, [])
        );
    }

    /**
     * @dataProvider getCalendarEventsProvider
     */
    public function testGetCalendarEvents($connections, $tasks)
    {
        $organizationId = 1;
        $userId         = 123;
        $calendarId     = 10;
        $start          = new \DateTime();
        $end            = new \DateTime();

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

        $query = $this->getMockBuilder('Doctrine\ORM\AbstractQuery')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->aclHelper->expects($this->once())
            ->method('apply')
            ->with($this->identicalTo($qb))
            ->will($this->returnValue($query));

        $this->doctrineHelper->expects($this->once())
            ->method('getEntityRepository')
            ->with('OroCRMTaskBundle:Task')
            ->will($this->returnValue($repo));

        $this->taskCalendarNormalizer->expects($this->once())
            ->method('getTasks')
            ->with(TaskCalendarProvider::MY_TASKS_CALENDAR_ID, $this->identicalTo($query))
            ->will($this->returnValue($tasks));

        $result = $this->provider->getCalendarEvents($organizationId, $userId, $calendarId, $start, $end, $connections);
        $this->assertEquals($tasks, $result);
    }

    public function getCalendarEventsProvider()
    {
        return [
            'no connections'          => [
                'connections' => [],
                'tasks'       => [['id' => 1]]
            ],
            'with visible connection' => [
                'connections' => [
                    [TaskCalendarProvider::MY_TASKS_CALENDAR_ID => true]
                ],
                'tasks'       => [['id' => 1]]
            ],
        ];
    }

    public function testGetCalendarEventsWithInvisibleConnection()
    {
        $organizationId = 1;
        $userId         = 123;
        $calendarId     = 10;
        $start          = new \DateTime();
        $end            = new \DateTime();
        $connections    = [TaskCalendarProvider::MY_TASKS_CALENDAR_ID => false];

        $this->taskCalendarNormalizer->expects($this->never())
            ->method('getTasks');

        $result = $this->provider->getCalendarEvents($organizationId, $userId, $calendarId, $start, $end, $connections);
        $this->assertEquals([], $result);
    }
}
