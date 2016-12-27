<?php

namespace Oro\Bundle\MagentoBundle\Provider;

/**
 * The real implementation of this class is at \Oro\Bridge\MarketingCRM\Provider\TrackingVisitEventProvider
 */
class TrackingVisitEventProvider implements TrackingVisitEventProviderInterface
{
    /**
     * @inheritdoc
     */
    public function getCustomerEventAggregates(array $customers, array $eventNames)
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getCustomerEventsCountByDate(array $customers, array $eventNames)
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getCustomerEventsCountByDateAndChannel(array $customers, array $eventNames)
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getMostViewedPage(array $customers = [])
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getLastViewedPage(array $customers = [])
    {
        return null;
    }
}
