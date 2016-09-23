<?php

namespace Oro\Bundle\MarketingListBundle\Entity;

interface MarketingListStateItemInterface
{
    /**
     * @return int
     */
    public function getEntityId();

    /**
     * @param int $entityId
     *
     * @return MarketingListStateItemInterface
     */
    public function setEntityId($entityId);

    /**
     * @param MarketingList $marketingList
     *
     * @return MarketingListStateItemInterface
     */
    public function setMarketingList(MarketingList $marketingList);

    /**
     * @return MarketingList
     */
    public function getMarketingList();
}
