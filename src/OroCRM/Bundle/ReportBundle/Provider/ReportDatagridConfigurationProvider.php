<?php

namespace OroCRM\Bundle\ReportBundle\Provider;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\DataGridBundle\Provider\ConfigurationProviderInterface;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;

class ReportDatagridConfigurationProvider implements ConfigurationProviderInterface
{
    const GRID_PREFIX      = 'oro_report_table_';

    /**
     * @var EntityManager
     */
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
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
        $repo   = $this->em->getRepository('OroCRMReportBundle:Report');
        $report = $repo->find($id);
        $builder = new ReportDatagridConfigurationBuilder($gridName, $report);

        return $builder->getConfiguration();
    }
}
