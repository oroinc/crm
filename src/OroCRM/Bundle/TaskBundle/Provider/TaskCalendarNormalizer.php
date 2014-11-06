<?php

namespace OroCRM\Bundle\TaskBundle\Provider;

use Doctrine\ORM\QueryBuilder;

class TaskCalendarNormalizer
{
    /**
     * @param int          $calendarId
     * @param QueryBuilder $qb
     * @return array
     */
    public function getTasks($calendarId, QueryBuilder $qb)
    {
        $items = $qb->getQuery()->getArrayResult();
        foreach ($items as $item) {
            $resultItem = [];
            $resultItem['calendar']     = $calendarId;
            $resultItem['id']           = 'task_' . $item['id'];
            $resultItem['title']        = $item['subject'];
            $resultItem['description']  = $item['description'];
            $resultItem['start']        = $item['dueDate']->format('c');
            $end = clone $item['dueDate'];
            $resultItem['end']          = $end->add(new \DateInterval('PT30M'))->format('c');
            $resultItem['allDay']       = false;
            $resultItem['createdAt']    = $item['createdAt']->format('c');
            $resultItem['updatedAt']    = $item['updatedAt']->format('c');
            $resultItem['editable']     = true;
            $resultItem['removable']    = true;
            $resultItem['reminders']    = [];
            $result[] = $resultItem;
        }

        return $result;
    }
}
