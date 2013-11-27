<?php

namespace OroCRM\Bundle\ReportBundle\Grid;

use Symfony\Bridge\Doctrine\ManagerRegistry;
use Oro\Bundle\DataGridBundle\Provider\ConfigurationProviderInterface;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;

class ReportDatagridConfigurationProvider implements ConfigurationProviderInterface
{
    const GRID_PREFIX      = 'oro_report_table_';

    /**
     * @var ManagerRegistry
     */
    protected $doctrine;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * {@inheritdoc}
     */
    public function isApplicable($gridName)
    {
        return (strpos($gridName, self::GRID_PREFIX) === 0);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration($gridName)
    {
        $id     = intval(substr($gridName, strlen(self::GRID_PREFIX)));
        $repo   = $this->doctrine->getRepository('OroCRMReportBundle:Report');
        $report = $repo->find($id);
        $builder = new ReportDatagridConfigurationBuilder($gridName, $report, $this->doctrine);

        return $builder->getConfiguration();
    }
}
