<?php

namespace OroCRM\Bundle\TaskBundle\Tests\Unit\Provider;

use Doctrine\ORM\Query\Expr;
use Oro\Bundle\CalendarBundle\Entity\Calendar;
use Oro\Bundle\CalendarBundle\Entity\CalendarProperty;
use Oro\Bundle\CalendarBundle\Provider\UserCalendarProvider;
use Oro\Bundle\CalendarBundle\Tests\Unit\ReflectionUtil;
use Oro\Bundle\UserBundle\Entity\User;

use OroCRM\Bundle\TaskBundle\Provider\TaskCalendarProvider;

class TaskCalendarProviderTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $doctrineHelper;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $taskCalendarNormalizer;

    /** @var bool */
    protected $enabled = true;

    /** @var TaskCalendarProvider */
    protected $provider;

    protected function setUp()
    {
        $this->doctrineHelper          = $this->getMockBuilder('Oro\Bundle\EntityBundle\ORM\DoctrineHelper')
            ->disableOriginalConstructor()
            ->getMock();
        $this->taskCalendarNormalizer =
            $this->getMockBuilder('OroCRM\Bundle\TaskBundle\Provider\TaskCalendarNormalizer')
                ->disableOriginalConstructor()
                ->getMock();

        $this->provider = new TaskCalendarProvider(
            $this->doctrineHelper,
            $this->taskCalendarNormalizer,
            $this->enabled
        );
    }

    public function testGetCalendarDefaultValues()
    {
        $userId = 123;
        $calendarId = 1;
        $this->assertEquals(array(
            $calendarId => array(
                'calendarName'  => 'My Tasks',
                'removable'     => false,
                'position'      => -1,
            )
        ), $this->provider->getCalendarDefaultValues($userId, $calendarId, array()));
    }

    public function testGetCalendarEvents()
    {
        $calendarId  = 123;
        $userId      = 123;
        $start       = new \DateTime();
        $end         = new \DateTime();
        $subordinate = true;
        $tasks       = [['id' => 1]];

        $qb   = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $repo = $this->getMockBuilder('OroCRM\Bundle\TaskBundle\Entity\Repository\TaskRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $this->doctrineHelper->expects($this->once())
            ->method('getEntityRepository')
            ->with('OroCRMTaskBundle:Task')
            ->will($this->returnValue($repo));
        $repo->expects($this->once())
            ->method('getTaskListByTimeIntervalQueryBuilder')
            ->with($calendarId, $this->identicalTo($start), $this->identicalTo($end))
            ->will($this->returnValue($qb));

        $this->taskCalendarNormalizer->expects($this->once())
            ->method('getTasks')
            ->with($calendarId, $this->identicalTo($qb))
            ->will($this->returnValue($tasks));

        $result = $this->provider->getCalendarEvents($userId, $calendarId, $start, $end, $subordinate);
        $this->assertEquals($tasks, $result);
    }
}
