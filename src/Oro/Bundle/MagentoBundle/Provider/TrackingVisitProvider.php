<?php

namespace Oro\Bundle\MagentoBundle\Provider;

use Doctrine\ORM\EntityRepository;

/**
 * The real implementation of this class is at \Oro\Bridge\MarketingCRM\Provider\TrackingVisitProvider
 */
class TrackingVisitProvider implements TrackingVisitProviderInterface
{
    /**
     * @inheritdoc
     */
    public function getDeeplyVisitedCount(\DateTime $from = null, \DateTime $to = null)
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getVisitedCount(\DateTime $from = null, \DateTime $to = null)
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getAggregates(array $customers)
    {
        return [
            'count' => null,
            'last' => null,
            'monthly' => null
        ];
    }

    /**
     * @return EntityRepository
     */
    protected function getTrackingVisitRepository()
    {
        return null;
    }
}
