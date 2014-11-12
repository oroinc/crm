<?php

namespace OroCRM\Bundle\TaskBundle\Provider;

use Doctrine\ORM\AbstractQuery;

use Oro\Bundle\ReminderBundle\Entity\Manager\ReminderManager;

class TaskCalendarNormalizer
{
    /** @var ReminderManager */
    protected $reminderManager;

    /**
     * @param ReminderManager $reminderManager
     */
    public function __construct(ReminderManager $reminderManager)
    {
        $this->reminderManager = $reminderManager;
    }

    /**
     * @param int           $calendarId
     * @param AbstractQuery $query
     *
     * @return array
     */
    public function getTasks($calendarId, AbstractQuery $query)
    {
        $result = [];

        $items  = $query->getArrayResult();
        foreach ($items as $item) {
            /** @var \DateTime $start */
            $start = $item['dueDate'];
            $end   = clone $start;
            $end   = $end->add(new \DateInterval('PT30M'));

            $result[] = [
                'calendar'    => $calendarId,
                'id'          => $item['id'],
                'title'       => $item['subject'],
                'description' => $item['description'],
                'start'       => $start->format('c'),
                'end'         => $end->format('c'),
                'allDay'      => false,
                'createdAt'   => $item['createdAt']->format('c'),
                'updatedAt'   => $item['updatedAt']->format('c'),
                'editable'    => false,
                'removable'   => false
            ];
        }

        $this->reminderManager->applyReminders($result, 'OroCRM\Bundle\TaskBundle\Entity\Task');

        return $result;
    }
}
