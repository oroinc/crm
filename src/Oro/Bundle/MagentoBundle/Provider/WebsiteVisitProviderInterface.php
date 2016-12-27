<?php

namespace Oro\Bundle\MagentoBundle\Provider;

interface WebsiteVisitProviderInterface
{
    /**
     * @param array $dateRange
     *
     * @return int
     */
    public function getSiteVisitsValues($dateRange);
}
