<?php

namespace OroCRM\Bundle\TaskBundle\Provider;

use Symfony\Component\Translation\TranslatorInterface;

use Oro\Bundle\CalendarBundle\Entity\CalendarProperty;
use Oro\Bundle\CalendarBundle\Entity\Repository\CalendarPropertyRepository;
use Oro\Bundle\CalendarBundle\Provider\CalendarProviderInterface;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use OroCRM\Bundle\TaskBundle\Entity\Repository\TaskRepository;

class TaskCalendarProvider implements CalendarProviderInterface
{
    const ALIAS = 'tasks';
    const MY_TASKS_CALENDAR_ID = 1;

    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /** @var AclHelper */
    protected $aclHelper;

    /** @var TaskCalendarNormalizer */
    protected $taskCalendarNormalizer;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var bool */
    protected $myTasksEnabled;

    /** @var  bool */
    protected $calendarLabels = [
        self::MY_TASKS_CALENDAR_ID => 'orocrm.task.menu.my_tasks'
    ];

    /**
     * @param DoctrineHelper         $doctrineHelper
     * @param AclHelper              $aclHelper
     * @param TaskCalendarNormalizer $taskCalendarNormalizer
     * @param TranslatorInterface    $translator
     * @param bool                   $myTasksEnabled
     */
    public function __construct(
        DoctrineHelper $doctrineHelper,
        AclHelper $aclHelper,
        TaskCalendarNormalizer $taskCalendarNormalizer,
        TranslatorInterface $translator,
        $myTasksEnabled
    ) {
        $this->doctrineHelper         = $doctrineHelper;
        $this->aclHelper              = $aclHelper;
        $this->taskCalendarNormalizer = $taskCalendarNormalizer;
        $this->translator             = $translator;
        $this->myTasksEnabled         = $myTasksEnabled;
    }

    /**
     * {@inheritdoc}
     */
    public function getCalendarDefaultValues($userId, $calendarId, array $calendarIds)
    {
        $result = [];

        if ($this->myTasksEnabled || in_array(self::MY_TASKS_CALENDAR_ID, $calendarIds)) {
            $result[self::MY_TASKS_CALENDAR_ID] = [
                'calendarName'    => $this->translator->trans($this->calendarLabels[self::MY_TASKS_CALENDAR_ID]),
                'removable'       => false,
                'position'        => -1,
                'backgroundColor' => 'F83A22',
                'widgetRoute'     => 'orocrm_task_widget_info',
                'widgetOptions'   => [
                    'title'         => $this->translator->trans('orocrm.task.info_widget_title'),
                    'dialogOptions' => [
                        'width' => 600
                    ]
                ]
            ];
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getCalendarName(CalendarProperty $connection)
    {
        return $this->translator->trans($this->calendarLabels[$connection->getCalendar()]);
    }

    /**
     * {@inheritdoc}
     */
    public function getCalendarEvents($userId, $calendarId, $start, $end, $subordinate)
    {
        if (!$this->myTasksEnabled) {
            return [];
        }

        /** @var CalendarPropertyRepository $connectionRepo */
        $connectionRepo = $this->doctrineHelper->getEntityRepository('OroCalendarBundle:CalendarProperty');
        $connections    = $connectionRepo->getConnectionsByTargetCalendarQueryBuilder($calendarId, self::ALIAS)
            ->select('connection.calendar, connection.visible')
            ->getQuery()
            ->getArrayResult();

        if ($this->isCalendarVisible($connections, self::MY_TASKS_CALENDAR_ID)) {
            /** @var TaskRepository $repo */
            $repo  = $this->doctrineHelper->getEntityRepository('OroCRMTaskBundle:Task');
            $qb    = $repo->getTaskListByTimeIntervalQueryBuilder($userId, $start, $end);
            $query = $this->aclHelper->apply($qb);

            return $this->taskCalendarNormalizer->getTasks(self::MY_TASKS_CALENDAR_ID, $query);
        }

        return [];
    }

    /**
     * @param array $connections
     * @param int   $calendarId
     * @param bool  $default
     *
     * @return bool
     */
    protected function isCalendarVisible($connections, $calendarId, $default = true)
    {
        $connection = null;
        foreach ($connections as $c) {
            if ($c['calendar'] === $calendarId) {
                $connection = $c;
                break;
            }
        }

        return $connection ? $connection['visible'] : $default;
    }
}
