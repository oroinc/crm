<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

use Oro\Bundle\BatchBundle\Item\InvalidItemException;
use Oro\Bundle\IntegrationBundle\Provider\AbstractConnector;

class RegionConnector extends AbstractConnector implements MagentoConnectorInterface
{
    const ENTITY_NAME         = 'OroCRM\\Bundle\\MagentoBundle\\Entity\\Region';
    const JOB_VALIDATE_IMPORT = 'mage_regions_import_validation';
    const JOB_IMPORT          = 'mage_regions_import';
    const CONNECTOR_LABEL     = 'orocrm.magento.connector.region.label';

    /** @var array */
    protected $countriesBuffer = false;

    /** @var array */
    protected $regionsBuffer = [];

    /** @var string */
    protected $currentCountry = '';

    /**
     * {@inheritdoc}
     */
    public function doRead()
    {
        if ($this->countriesBuffer === false) {
            $this->findCountriesToImport();
        }

        if (empty($this->countriesBuffer)) {
            return null;
        }

        if (empty($this->regionsBuffer)) {
            $this->currentCountry = $country = (array)array_shift($this->countriesBuffer);

            $now = new \DateTime('now', new \DateTimeZone('UTC'));
            $this->logger->info(sprintf("%s loading country %s: ", $now->format('d-m-Y H:i:s'), $country['name']));

            $data = $this->getRegionsData($country['iso2_code']);
            $this->logger->info(sprintf('found %d entities', count($data)));

            // will skip further processing
            if (empty($data)) {
                throw new InvalidItemException("No regions found", []);
            }
        }

        $region                = array_shift($this->regionsBuffer);
        $region['countryCode'] = $this->currentCountry['iso2_code'];

        return $region;
    }

    /**
     * Fill countries list to fill buffer
     *
     * @return bool|null
     */
    protected function findCountriesToImport()
    {
        $this->countriesBuffer = $this->getCountryList();

        $this->logger->info(sprintf('found %d countries', count($this->countriesBuffer)));
    }

    /**
     * {@inheritdoc}
     */
    public function getCountryList()
    {
        return $this->call('directoryCountryList');
    }

    /**
     * {@inheritdoc}
     */
    public function getRegionsData($iso2Code)
    {
        if (empty($this->regionsBuffer)) {
            $result  = (array)$this->call('directoryRegionList', $iso2Code);
            $regions = [];

            foreach ($result as $obj) {
                $regions[$obj->code] = (array)$obj;
            }

            $this->regionsBuffer = $regions;
        }

        return $this->regionsBuffer;
    }
}
