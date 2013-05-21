<?php

namespace Oro\Bundle\SearchBundle\Datagrid;

use Oro\Bundle\GridBundle\EventDispatcher\ResultDatagridEvent;

class EntityResultListener
{
    /**
     * @var string
     */
    protected $datagridName;

    /**
     * @param string $datagridName
     */
    public function __construct($datagridName)
    {
        $this->datagridName = $datagridName;
    }

    /**
     * @param ResultDatagridEvent $event
     */
    public function processResult(ResultDatagridEvent $event)
    {
        if (!$event->isDatagridName($this->datagridName)) {
            return;
        }

        // TODO implement main logic
    }
}
