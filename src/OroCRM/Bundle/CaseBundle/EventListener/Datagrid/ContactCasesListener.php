<?php

namespace OroCRM\Bundle\CaseBundle\EventListener\Datagrid;

use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\FilterBundle\Grid\Extension\Configuration;

class ContactCasesListener
{
    const CONTACT_FILTER_NAME = 'contact';

    /**
     * @param BuildBefore $event
     */
    public function onBuildBefore(BuildBefore $event)
    {
        $config = $event->getConfig();
        $path = sprintf('%s[%s]', Configuration::COLUMNS_PATH, self::CONTACT_FILTER_NAME);
        $config->offsetUnsetByPath($path);
    }
}
