<?php

namespace OroCRM\Bundle\MarketingListBundle\Acl\Voter;

use Oro\Bundle\SecurityBundle\Acl\Voter\AbstractEntityVoter;
use OroCRM\Bundle\MarketingListBundle\Entity\MarketingList;

class MarketingListSegmentVoter extends AbstractEntityVoter
{
    const SEGMENT_ENTITY = 'Oro\Bundle\SegmentBundle\Entity\Segment';

    /**
     * @var array
     */
    protected $supportedAttributes = array('EDIT', 'DELETE');

    /**
     * @var array
     */
    protected $marketingListBySegment = array();

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return $class === self::SEGMENT_ENTITY;
    }

    /**
     * {@inheritdoc}
     */
    protected function getPermissionForAttribute($class, $identifier, $attribute)
    {
        if ($this->getMarketingListBySegment($identifier)) {
            $permission = self::ACCESS_DENIED;
        } else {
            $permission = self::ACCESS_ABSTAIN;
        }

        return $permission;
    }

    /**
     * @param int $segmentId
     * @return MarketingList|null
     */
    protected function getMarketingListBySegment($segmentId)
    {
        if (empty($this->marketingListBySegment[$segmentId])) {
            $segment = $this->doctrineHelper->getEntityReference(self::SEGMENT_ENTITY, $segmentId);
            $marketingList = $this->registry->getManager()
                ->getRepository('OroCRMMarketingListBundle:MarketingList')
                ->findOneBy(array('segment' => $segment));
            $this->marketingListBySegment[$segmentId] = $marketingList;
        }

        return $this->marketingListBySegment[$segmentId];
    }
}
