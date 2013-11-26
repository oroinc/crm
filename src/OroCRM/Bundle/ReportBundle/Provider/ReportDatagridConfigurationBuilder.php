<?php

namespace OroCRM\Bundle\ReportBundle\Provider;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\QueryDesignerBundle\Provider\DatagridConfigurationBuilder;
use OroCRM\Bundle\ReportBundle\Entity\Report;

class ReportDatagridConfigurationBuilder extends DatagridConfigurationBuilder
{
    public function __construct($gridName, Report $report)
    {
        parent::__construct($gridName, $report);

        $this->config->offsetSetByPath('[source][acl_resource]', 'orocrm_report_view');
    }
}
