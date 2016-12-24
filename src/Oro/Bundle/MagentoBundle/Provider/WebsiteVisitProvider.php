<?php

namespace Oro\Bundle\MagentoBundle\Provider;

/**
 * The real implementation of this class is at \Oro\Bridge\MarketingCRM\Provider\WebsiteVisitProvider
 */
class WebsiteVisitProvider implements WebsiteVisitProviderInterface
{
    /**
     * @inheritdoc
     */
    public function getSiteVisitsValues($dateRange)
    {
        return 0;
    }
}
