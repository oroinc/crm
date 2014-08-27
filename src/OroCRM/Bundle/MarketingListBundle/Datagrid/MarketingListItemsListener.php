<?php

namespace OroCRM\Bundle\MarketingListBundle\Datagrid;

use Symfony\Bridge\Doctrine\ManagerRegistry;

use Oro\Bundle\DataGridBundle\Event\PreBuild;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\SegmentBundle\Entity\Segment;
use OroCRM\Bundle\MarketingListBundle\Entity\MarketingList;
use OroCRM\Bundle\MarketingListBundle\Model\DataGridConfigurationHelper;

class MarketingListItemsListener
{
    const MARKETING_LIST = 'OroCRM\Bundle\MarketingListBundle\Entity\MarketingList';
    const MIXIN_NAME = 'orocrm-marketing-list-items-mixin';

    /**
     * @var ManagerRegistry
     */
    protected $managerRegistry;

    /**
     * @var DataGridConfigurationHelper
     */
    protected $dataGridConfigurationHelper;

    /**
     * @var MarketingList[]
     */
    protected $marketingListsBySegment = array();

    /**
     * @var array
     */
    protected $appliedFor = array();

    /**
     * @param ManagerRegistry $managerRegistry
     * @param DataGridConfigurationHelper $dataGridConfigurationHelper
     */
    public function __construct(
        ManagerRegistry $managerRegistry,
        DataGridConfigurationHelper $dataGridConfigurationHelper
    ) {
        $this->managerRegistry = $managerRegistry;
        $this->dataGridConfigurationHelper = $dataGridConfigurationHelper;
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
                $segmentId = $this->getSegmentIdByGridName($datagrid->getName());
                $marketingList = $this->getMarketingListBySegment($segmentId);

                $queryBuilder = $dataSource->getQueryBuilder();
                $queryBuilder
                    ->addSelect($marketingList->getId() . ' as marketingList')
                    ->setParameter('marketingListEntity', $marketingList);
            }
        }
    }

    /**
     * @param string $gridName
     * @return bool
     */
    public function isApplicable($gridName)
    {
        $segmentId = $this->getSegmentIdByGridName($gridName);
        return $segmentId && (bool)$this->getMarketingListBySegment($segmentId);
    }

    /**
     * @param string $gridName
     * @return int|null
     */
    protected function getSegmentIdByGridName($gridName)
    {
        if (strpos($gridName, Segment::GRID_PREFIX) === false) {
            return null;
        }

        $segmentId = (int)str_replace(Segment::GRID_PREFIX, '', $gridName);
        if (empty($segmentId)) {
            return null;
        }

        return $segmentId;
    }

    /**
     * @param int $segmentId
     * @return MarketingList
     */
    protected function getMarketingListBySegment($segmentId)
    {
        if (empty($this->marketingListsBySegment[$segmentId])) {
            $this->marketingListsBySegment[$segmentId] = $this->managerRegistry
                ->getManagerForClass(self::MARKETING_LIST)
                ->getRepository(self::MARKETING_LIST)
                ->findOneBy(['segment' => $segmentId]);
        }

        return $this->marketingListsBySegment[$segmentId];
    }
}
