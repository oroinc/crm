<?php

namespace Oro\Bundle\MagentoBundle\Provider;

use Oro\Bundle\MagentoBundle\Entity\Customer;

interface TrackingVisitProviderInterface
{
    /**
     * @param \DateTime $from
     * @param \DateTime $to
     *
     * @return int
     */
    public function getDeeplyVisitedCount(\DateTime $from = null, \DateTime $to = null);

    /**
     * @param \DateTime $from
     * @param \DateTime $to
     *
     * @return int|null
     */
    public function getVisitedCount(\DateTime $from = null, \DateTime $to = null);

    /**
     * Return total number of visits, last visit date and visits per month
     * filtered by customers
     *
     * @param Customer[] $customers
     *
     * @return array
     */
    public function getAggregates(array $customers);
}
