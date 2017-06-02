<?php

namespace Oro\Bundle\MagentoBundle\Provider\Iterator;

abstract class AbstractRegionIterator extends AbstractPageableIterator
{
    /** @var array */
    protected $countries = [];

    /** @var array */
    protected $regions = [];

    /** @var array */
    protected $currentCountry;

    /**
     * {@inheritdoc}
     */
    protected function findEntitiesToProcess()
    {
        if (empty($this->countries)) {
            $this->countries = $this->getCountryList();
        }

        if (key($this->countries) === null) {
            return null;
        }

        if (empty($this->entitiesIdsBuffer)) {
            $this->currentCountry = (array)current($this->countries);
            next($this->countries);

            $this->entitiesIdsBuffer = $this->getEntityIds();
            $this->logger->info(sprintf('found %d entities', count($this->entitiesIdsBuffer)));
        }

        return true;
    }

    /**
     * @return array
     */
    abstract protected function getCountryList();

    /**
     * {@inheritdoc}
     */
    protected function getEntity($id)
    {
        $region                = $this->regions[$id];
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
