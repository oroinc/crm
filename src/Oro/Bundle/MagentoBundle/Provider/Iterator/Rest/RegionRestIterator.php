<?php

namespace Oro\Bundle\MagentoBundle\Provider\Iterator\Rest;

use Oro\Bundle\MagentoBundle\Provider\Iterator\AbstractRegionIterator;

class RegionRestIterator extends AbstractRegionIterator
{
    /**
     * {@inheritdoc}
     */
    protected function getCountryList()
    {
        return $this->transport->doGetRegionsRequest();
    }

    /**
     * {@inheritdoc}
     */
    protected function getEntityIds()
    {
        $this->regions = [];
        if (isset($this->currentCountry['available_regions'])) {
            $result   = $this->currentCountry['available_regions'];

            foreach ($result as $region) {
                $this->regions[$region['code']] = $region;
            }
        }

        return array_keys($this->regions);
    }
}
