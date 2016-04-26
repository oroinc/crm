<?php

namespace OroCRM\Bundle\ReportBundle\EventListener\Datagrid;

use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;

use OroCRM\Bundle\SalesBundle\Entity\Opportunity;

class OpportunitiesByStateReportListener
{
    /**
     * @param BuildBefore $event
     */
    public function onBuildBefore(BuildBefore $event)
    {
        $className = ExtendHelper::buildEnumValueClassName(Opportunity::INTERNAL_STATE_CODE);
        $config = $event->getConfig();
        $from[] = [
            'table' => $className,
            'alias' => 'status'
        ];
        $config->offsetSetByPath('[source][query][from]', $from);
    }
}
