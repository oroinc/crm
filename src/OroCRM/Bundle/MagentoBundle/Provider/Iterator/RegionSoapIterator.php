<?php

namespace OroCRM\Bundle\MagentoBundle\Provider\Iterator;

use OroCRM\Bundle\MagentoBundle\Provider\Transport\SoapTransport;

class RegionSoapIterator extends AbstractPageableSoapIterator
{
    /** @var array */
    protected $countriesBuffer;

    /** @var array */
    protected $regionsBuffer = [];

    /** @var array */
    protected $currentCountry;

    /**
     * {@inheritdoc}
     */
    protected function findEntitiesToProcess()
    {
        if (empty($this->countriesBuffer)) {
            $this->countriesBuffer = $this->transport->call(SoapTransport::ACTION_COUNTRY_LIST);
        }

        if (key($this->countriesBuffer) === null) {
            return null;
        }

        if (empty($this->entitiesIdsBuffer)) {
            $this->currentCountry = (array)current($this->countriesBuffer);
            next($this->countriesBuffer);

            $this->entitiesIdsBuffer = $this->getEntityIds();
            $this->logger->info(sprintf('found %d entities', count($this->entitiesIdsBuffer)));
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    protected function getEntityIds()
    {
        $iso2Code = $this->currentCountry['iso2_code'];
        $result   = (array)$this->transport->call(SoapTransport::ACTION_REGION_LIST, ['country' => $iso2Code]);

        $this->regionsBuffer = [];
        foreach ($result as $obj) {
            $this->regionsBuffer[$obj->code] = (array)$obj;
        }

        return array_keys($this->regionsBuffer);
    }

    /**
     * {@inheritdoc}
     */
    protected function getEntity($id)
    {
        $region                = $this->regionsBuffer[$id];
        $region['countryCode'] = $this->currentCountry['iso2_code'];

        return $region;
    }

    /**
     * {@inheritdoc}
     */
    protected function getIdFieldName()
    {
        return 'code';
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        $this->logger->info(sprintf('Loading Region by id: %s', $this->key()));

        return $this->current;
    }
}
