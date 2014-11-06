<?php

namespace OroCRM\Bundle\TaskBundle\Provider;

use Oro\Bundle\CalendarBundle\Entity\CalendarProperty;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\CalendarBundle\Provider\CalendarProviderInterface;
use Oro\Bundle\LocaleBundle\Formatter\NameFormatter;

use OroCRM\Bundle\TaskBundle\Entity\Repository\TaskRepository;

class TaskCalendarProvider implements CalendarProviderInterface
{
    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /** @var TaskCalendarNormalizer */
    protected $taskCalendarNormalizer;

    /** @var  bool */
    protected $enabled;

    /**
     * @param DoctrineHelper          $doctrineHelper
     * @param TaskCalendarNormalizer $taskCalendarNormalizer
     * @param bool                    $enabled
     */
    public function __construct(
        DoctrineHelper $doctrineHelper,
        TaskCalendarNormalizer $taskCalendarNormalizer,
        $enabled
    ) {
        $this->doctrineHelper          = $doctrineHelper;
        $this->taskCalendarNormalizer  = $taskCalendarNormalizer;
        $this->enabled                 = $enabled;
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
            $calendarId => [
                'calendarName'  => 'My Tasks',
                'removable'     => false,
                'position'      => -1,
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getCalendarName(CalendarProperty $connection)
    {
        return 'My Tasks';
    }

    /**
     * {@inheritdoc}
     */
    public function getCalendarEvents($userId, $calendarId, $start, $end, $subordinate)
    {
        $result = [];

        if (!$this->enabled) {
            return $result;
        }

        /** @var TaskRepository $repo */
        $repo = $this->doctrineHelper->getEntityRepository('OroCRMTaskBundle:Task');
        $qb = $repo->getTaskListByTimeIntervalQueryBuilder($userId, $start, $end);

        return $this->taskCalendarNormalizer->getTasks($calendarId, $qb);
    }
}
