<?php

namespace OroCRM\Bundle\MarketingListBundle\Acl\Voter;

use Oro\Bundle\SecurityBundle\Acl\Voter\AbstractEntityVoter;
use OroCRM\Bundle\MarketingListBundle\Entity\MarketingList;

class MarketingListSegmentVoter extends AbstractEntityVoter
{
    /**
     * @var array
     */
    protected $supportedAttributes = ['EDIT', 'DELETE'];

    /**
     * @var array
     */
    protected $marketingListBySegment = [];

    /**
     * {@inheritdoc}
     */
    protected function getPermissionForAttribute($class, $identifier, $attribute)
    {
        if ($this->getMarketingListBySegment($identifier)) {
            return self::ACCESS_DENIED;
        }

        return self::ACCESS_ABSTAIN;
    }

    /**
     * @param int $segmentId
     * @return MarketingList|null
     */
    protected function getMarketingListBySegment($segmentId)
    {
        if (empty($this->marketingListBySegment[$segmentId])) {
            $segment = $this->doctrineHelper->getEntityReference($this->className, $segmentId);
            $marketingList = $this->doctrineHelper
                ->getEntityRepository('OroCRMMarketingListBundle:MarketingList')
                ->findOneBy(['segment' => $segment]);
            $this->marketingListBySegment[$segmentId] = $marketingList;
        }

        return $this->marketingListBySegment[$segmentId];
    }
}
