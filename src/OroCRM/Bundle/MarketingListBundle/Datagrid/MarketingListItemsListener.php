<?php

namespace OroCRM\Bundle\MarketingListBundle\Datagrid;

use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\DataGridBundle\Event\PreBuild;
use OroCRM\Bundle\MarketingListBundle\Model\DataGridConfigurationHelper;
use OroCRM\Bundle\MarketingListBundle\Model\MarketingListSegmentHelper;

class MarketingListItemsListener
{
    const MIXIN_NAME = 'orocrm-marketing-list-items-mixin';

    /**
     * @var DataGridConfigurationHelper
     */
    protected $dataGridConfigurationHelper;

    /**
     * @var MarketingListSegmentHelper
     */
    protected $segmentHelper;

    /**
     * @var array
     */
    protected $appliedFor = [];

    /**
     * @param DataGridConfigurationHelper $dataGridConfigurationHelper
     * @param MarketingListSegmentHelper  $segmentHelper
     */
    public function __construct(
        DataGridConfigurationHelper $dataGridConfigurationHelper,
        MarketingListSegmentHelper $segmentHelper
    ) {
        $this->dataGridConfigurationHelper = $dataGridConfigurationHelper;
        $this->segmentHelper               = $segmentHelper;
    }

    /**
     * @param PreBuild $event
     */
    public function onPreBuild(PreBuild $event)
    {
        $gridName = $event->getConfig()->getName();
        if ($this->isApplicable($gridName) && empty($this->appliedFor[$gridName])) {
            $this->dataGridConfigurationHelper->extendConfiguration($event->getConfig(), self::MIXIN_NAME);
            $this->appliedFor[$gridName] = true;
        }
    }

    /**
     * @param BuildAfter $event
     */
    public function onBuildAfter(BuildAfter $event)
    {
        $datagrid = $event->getDatagrid();
        if ($this->isApplicable($datagrid->getName())) {
            $dataSource = $event->getDatagrid()->getDatasource();

            if ($dataSource instanceof OrmDatasource) {
                $segmentId     = $this->segmentHelper->getSegmentIdByGridName($datagrid->getName());
                $marketingList = $this->segmentHelper->getMarketingListBySegment($segmentId);

                $queryBuilder = $dataSource->getQueryBuilder();
                $queryBuilder
                    ->addSelect($marketingList->getId() . ' as marketingList')
                    ->setParameter('marketingListEntity', $marketingList);
            }
        }
    }

    /**
     * @param string $gridName
     *
     * @return bool
     */
    public function isApplicable($gridName)
    {
        $segmentId = $this->segmentHelper->getSegmentIdByGridName($gridName);

        return $segmentId && (bool)$this->segmentHelper->getMarketingListBySegment($segmentId);
    }
}
