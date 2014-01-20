<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Iterator;

use OroCRM\Bundle\MagentoBundle\Provider\Transport\SoapTransport;

class RegionSoapIterator extends AbstractPageableSoapIterator
{
    /** @var array */
    protected $countriesBuffer = null;

    /** @var array */
    protected $regionsBuffer = [];

    /** @var array */
    protected $currentCountry;

    /**
     * {@inheritdoc}
     */
    protected function findEntitiesToProcess()
    {
        if ($this->countriesBuffer === null) {
            $this->countriesBuffer = $this->transport->call(SoapTransport::ACTION_COUNTRY_LIST);
        }

        if (empty($this->countriesBuffer)) {
            return null;
        }

        if (empty($this->regionsBuffer)) {
            $this->currentCountry = (array)array_shift($this->countriesBuffer);

            $this->entitiesIdsBuffer = $this->getEntityIds();
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function getEntityIds()
    {
        $iso2Code = $this->currentCountry['iso2_code'];
        $result   = (array)$this->transport->call(SoapTransport::ACTION_REGION_LIST, [$iso2Code]);

        $regions = [];
        foreach ($result as $obj) {
            $regions[$obj->code] = (array)$obj;
        }

        $this->regionsBuffer = $regions;

        return array_keys($regions);
    }

    /**
     * {@inheritdoc}
     */
    protected function getEntity($id)
    {
        $region                = $this->regionsBuffer[array_search($id, $this->regionsBuffer)];
        $region['countryCode'] = $this->currentCountry['iso2_code'];
    }

    /**
     * {@inheritdoc}
     */
    protected function getIdFieldName()
    {
        return 'code';
    }
}
