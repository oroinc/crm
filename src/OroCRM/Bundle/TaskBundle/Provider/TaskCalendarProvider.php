<?php

namespace OroCRM\Bundle\TaskBundle\Provider;

use Oro\Bundle\CalendarBundle\Entity\CalendarProperty;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\CalendarBundle\Provider\CalendarProviderInterface;

use OroCRM\Bundle\TaskBundle\Entity\Repository\TaskRepository;
use Symfony\Component\Translation\TranslatorInterface;

class TaskCalendarProvider implements CalendarProviderInterface
{
    const MY_TASKS_CALENDAR_ID = 1;

    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /** @var TaskCalendarNormalizer */
    protected $taskCalendarNormalizer;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var  bool */
    protected $enabled;

    /** @var  bool */
    protected $calendarLabels = [
        self::MY_TASKS_CALENDAR_ID => 'orocrm.task.menu.my_tasks'
    ];

    /**
     * @param DoctrineHelper         $doctrineHelper
     * @param TaskCalendarNormalizer $taskCalendarNormalizer
     * @param TranslatorInterface    $translator
     * @param bool                   $enabled
     */
    public function __construct(
        DoctrineHelper $doctrineHelper,
        TaskCalendarNormalizer $taskCalendarNormalizer,
        TranslatorInterface $translator,
        $enabled
    ) {
        $this->doctrineHelper         = $doctrineHelper;
        $this->taskCalendarNormalizer = $taskCalendarNormalizer;
        $this->translator             = $translator;
        $this->enabled                = $enabled;
    }

    /**
     * {@inheritdoc}
     */
    public function getCalendarDefaultValues($userId, $calendarId, array $calendarIds)
    {
        if (!$this->enabled) {
            return [];
        }

        return [
            self::MY_TASKS_CALENDAR_ID => [
                'calendarName'    => $this->translator->trans($this->calendarLabels[self::MY_TASKS_CALENDAR_ID]),
                'removable'       => false,
                'position'        => -1,
                'backgroundColor' => 'F83A22',
                'widgetRoute'     => 'orocrm_task_widget_info',
                'widgetOptions'   => [
                    'title'         => $this->translator->trans('orocrm.task.view_entity'),
                    'dialogOptions' => [
                        'width' => 600
                    ]
                ]
            ]
        ];
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
        if (!$this->enabled) {
            return [];
        }

        /** @var TaskRepository $repo */
        $repo = $this->doctrineHelper->getEntityRepository('OroCRMTaskBundle:Task');
        $qb   = $repo->getTaskListByTimeIntervalQueryBuilder($userId, $start, $end);

        return $this->taskCalendarNormalizer->getTasks(self::MY_TASKS_CALENDAR_ID, $qb);
    }
}
