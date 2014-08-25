<?php

namespace OroCRM\Bundle\MarketingListBundle\Datagrid;

use Symfony\Bridge\Doctrine\ManagerRegistry;

use Oro\Bundle\DataGridBundle\Event\PreBuild;
use Oro\Bundle\SegmentBundle\Entity\Segment;
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
        if ($this->isApplicable($gridName)) {
            $this->dataGridConfigurationHelper->extendConfiguration($event->getConfig(), self::MIXIN_NAME);
        }
    }

    /**
     * @param string $gridName
     * @return bool
     */
    public function isApplicable($gridName)
    {
        if (strpos($gridName, Segment::GRID_PREFIX) === false) {
            return false;
        }

        $segmentId = (int)str_replace(Segment::GRID_PREFIX, '', $gridName);
        if (empty($segmentId)) {
            return false;
        }

        $entity = $this->managerRegistry
            ->getManagerForClass(self::MARKETING_LIST)
            ->getRepository(self::MARKETING_LIST)
            ->findOneBy(['segment' => $segmentId]);

        return (bool)$entity;
    }
}
