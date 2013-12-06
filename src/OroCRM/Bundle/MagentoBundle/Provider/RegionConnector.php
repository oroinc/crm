<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

use Oro\Bundle\BatchBundle\Item\InvalidItemException;
use Oro\Bundle\IntegrationBundle\Provider\AbstractConnector;

class RegionConnector extends AbstractConnector
{
    const ENTITY_NAME         = 'OroCRM\\Bundle\\MagentoBundle\\Entity\\Region';
    const JOB_VALIDATE_IMPORT = 'mage_regions_import_validation';
    const JOB_IMPORT          = 'mage_regions_import';
    const ALIAS_REGIONS       = 'regions';

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
            $this->currentCountry = $country = (array) array_shift($this->countriesBuffer);

            // TODO: log
            $now = new \DateTime('now', new \DateTimeZone('UTC'));
            echo $now->format('d-m-Y H:i:s') . " loading country " . $country['name'] . ': ';

            $data = $this->getRegionsData($country['iso2_code']);

            echo count($data) . "\n";

            // will skip further processing
            if (empty($data)) {
                throw new InvalidItemException("No regions found", []);
            }
        }

        $region = array_shift($this->regionsBuffer);
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

        // TODO: remove / log
        echo sprintf('found %d countries', count($this->countriesBuffer)) . "\n";

        // no more data to look for
        if (empty($this->countriesBuffer)) {
            $result = false;
        } else {
            $result = $this->countriesBuffer;
        }

        return $result;
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
            $result = (array) $this->call('directoryRegionList', $iso2Code);
            $regions = [];

            foreach ($result as $obj) {
                $regions[$obj->code] = (array)$obj;
            }

            $this->regionsBuffer = $regions;
        }

        return $this->regionsBuffer;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel()
    {
        return 'orocrm.magento.connector.region.label';
    }

    /**
     * {@inheritdoc}
     */
    public function getImportEntityFQCN()
    {
        return self::ENTITY_NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getImportJobName($isValidationOnly = false)
    {
        if ($isValidationOnly) {
            return self::JOB_VALIDATE_IMPORT;
        }

        return self::JOB_IMPORT;
    }
}
