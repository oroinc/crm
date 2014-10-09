<?php

namespace OroCRM\Bundle\MarketingListBundle\Model;

use Doctrine\Common\Persistence\ManagerRegistry;

use Oro\Bundle\SegmentBundle\Entity\Segment;
use OroCRM\Bundle\MarketingListBundle\Entity\MarketingList;
use OroCRM\Bundle\MarketingListBundle\Grid\ConfigurationProvider;

class MarketingListSegmentHelper
{
    const MARKETING_LIST = 'OroCRM\Bundle\MarketingListBundle\Entity\MarketingList';

    /**
     * @var ManagerRegistry
     */
    protected $managerRegistry;

    /**
     * @var MarketingList[]
     */
    protected $marketingListsBySegment = [];

    /**
     * @param ManagerRegistry $managerRegistry
     */
    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * @param string $gridName
     *
     * @return int|null
     */
    public function getSegmentIdByGridName($gridName)
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
     * @deprecated
     *
     * @return MarketingList
     */
    public function getMarketingListBySegment($segmentId)
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
