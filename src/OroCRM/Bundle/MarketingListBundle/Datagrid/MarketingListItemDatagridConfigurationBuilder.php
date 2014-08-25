<?php

namespace OroCRM\Bundle\MarketingListBundle\Datagrid;

use Oro\Bundle\SegmentBundle\Entity\Segment;
use Oro\Bundle\SegmentBundle\Grid\SegmentDatagridConfigurationBuilder;
use OroCRM\Bundle\MarketingListBundle\Model\DataGridConfigurationHelper;

class MarketingListItemDatagridConfigurationBuilder extends SegmentDatagridConfigurationBuilder
{
    const MARKETING_LIST = 'OroCRM\Bundle\MarketingListBundle\Entity\MarketingList';
    const GRID_NAME = 'orocrm-marketing-list-items-grid';

    /**
     * @var DataGridConfigurationHelper
     */
    protected $dataGridConfigurationHelper;

    /**
     * @param DataGridConfigurationHelper $dataGridConfigurationHelper
     */
    public function setConfigurationHelper(DataGridConfigurationHelper $dataGridConfigurationHelper)
    {
        $this->dataGridConfigurationHelper = $dataGridConfigurationHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration()
    {
        return $this->dataGridConfigurationHelper->extendConfiguration(parent::getConfiguration(), self::GRID_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function isApplicable($gridName)
    {
        if (!parent::isApplicable($gridName)) {
            return false;
        }

        $segmentId = str_replace(Segment::GRID_PREFIX, '', $gridName);
        if (empty($segmentId)) {
            return false;
        }

        $entity = $this->doctrine
            ->getManagerForClass(self::MARKETING_LIST)
            ->getRepository(self::MARKETING_LIST)
            ->findOneBy(['segment' => $segmentId]);

        return (bool)$entity;
    }
}
