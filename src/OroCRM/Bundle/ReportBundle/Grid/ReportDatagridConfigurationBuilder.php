<?php

namespace OroCRM\Bundle\ReportBundle\Grid;

use Symfony\Bridge\Doctrine\ManagerRegistry;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\QueryDesignerBundle\Grid\DatagridConfigurationBuilder;
use OroCRM\Bundle\ReportBundle\Entity\Report;

class ReportDatagridConfigurationBuilder extends DatagridConfigurationBuilder
{
    public function __construct($gridName, Report $report, ManagerRegistry $doctrine)
    {
        parent::__construct($gridName, $report, $doctrine);

        $this->config->offsetSetByPath('[source][acl_resource]', 'orocrm_report_view');
    }
}
