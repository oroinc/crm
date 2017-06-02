<?php

namespace Oro\Bundle\MagentoBundle\Provider\Iterator\Soap;

use Oro\Bundle\MagentoBundle\Provider\Iterator\AbstractRegionIterator;
use Oro\Bundle\MagentoBundle\Provider\Transport\SoapTransport;

class RegionSoapIterator extends AbstractRegionIterator
{
    /**
     * {@inheritdoc}
     */
    protected function getCountryList()
    {
        return $this->transport->call(SoapTransport::ACTION_COUNTRY_LIST);
    }

    /**
     * {@inheritdoc}
     */
    protected function getRegionList($iso2Code)
    {
        return (array)$this->transport->call(SoapTransport::ACTION_REGION_LIST, ['country' => $iso2Code]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getEntityIds()
    {
        $iso2Code = $this->currentCountry['iso2_code'];
        $result   = $this->getRegionList($iso2Code);

        $this->regions = [];
        foreach ($result as $obj) {
            $this->regions[$obj->code] = (array)$obj;
        }

        return array_keys($this->regions);
    }
}
