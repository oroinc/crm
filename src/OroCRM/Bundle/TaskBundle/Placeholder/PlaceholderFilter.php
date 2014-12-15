<?php

namespace OroCRM\Bundle\TaskBundle\Placeholder;

use Oro\Bundle\CalendarBundle\Entity\Calendar;

class PlaceholderFilter
{
    /** @var bool */
    protected $myTasksEnabled;

    /**
     * @param bool $myTasksEnabled
     */
    public function __construct($myTasksEnabled)
    {
        $this->myTasksEnabled = $myTasksEnabled;
    }

    /**
     * Checks if Tasks button can be displayed on My Calendar page
     *
     * @param object $obj
     *
     * @return bool
     */
    public function isCalendarTasksVisible($obj)
    {
        return $this->myTasksEnabled && ($obj instanceof Calendar);
    }
}
