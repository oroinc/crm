<?php

namespace Oro\Bundle\MagentoBundle\Provider\Iterator\Soap;

use Oro\Bundle\MagentoBundle\Entity\Website;
use Oro\Bundle\MagentoBundle\Provider\Iterator\AbstractPageableIterator;

use Oro\Bundle\MagentoBundle\Utils\WSIUtils;

abstract class AbstractPageableSoapIterator extends AbstractPageableIterator
{
    /**
     * @param mixed $response
     *
     * @return array
     */
    protected function processCollectionResponse($response)
    {
        return WSIUtils::processCollectionResponse($response);
    }

    /**
     * @param array $response
     * @return array
     */
    protected function convertResponseToMultiArray($response)
    {
        return WSIUtils::convertResponseToMultiArray($response);
    }

    /**
     * @inheritdoc
     */
    protected function applyWebsiteFilters(array $websiteIds, array $storeIds)
    {
        if ($this->websiteId !== Website::ALL_WEBSITES) {
            if (!empty($websiteIds)) {
                $this->filter->addWebsiteFilter($websiteIds);
            }

            if (!empty($storeIds)) {
                $this->filter->addStoreFilter($storeIds);
            }
        }
    }
}
