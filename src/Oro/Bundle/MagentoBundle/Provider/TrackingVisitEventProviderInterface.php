<?php

namespace Oro\Bundle\MagentoBundle\Provider;

use Oro\Bundle\MagentoBundle\Entity\Customer;

interface TrackingVisitEventProviderInterface
{
    /**
     * @param Customer[] $customers
     * @param string[] $eventNames
     * @return array
     */
    public function getCustomerEventAggregates(array $customers, array $eventNames);

    /**
     * @param Customer[] $customers
     * @param string[] $eventNames
     * @return array
     */
    public function getCustomerEventsCountByDate(array $customers, array $eventNames);

    /**
     * @param Customer[] $customers
     * @param string[] $eventNames
     * @return array
     */
    public function getCustomerEventsCountByDateAndChannel(array $customers, array $eventNames);

    /**
     * Get the most viewed page filtered by customer ids
     * Returns array containing title, url, cnt
     *
     * @param Customer[] $customers Filter by customers
     * @return array
     */
    public function getMostViewedPage(array $customers = []);

    /**
     * Get the last viewed page filtered by customers
     * Returns array containing title, url
     *
     * @param Customer[] $customers Filter by customers
     * @return array
     */
    public function getLastViewedPage(array $customers = []);
}
