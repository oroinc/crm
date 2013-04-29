<?php

namespace Oro\Bundle\AddressBundle\Provider\ImportExport;

use Symfony\Component\Intl\Intl;
use Symfony\Component\Security\Acl\Exception\Exception;
use Symfony\Component\Yaml\Yaml;

class IntlReader extends Reader implements ReaderInterface
{
    const FALLBACK_DATA = 'DataFixtures/countries.yml';

    /**
     * @var string
     */
    protected $class;

    /**
     * @param string $class
     * @param null|int $batchSize
     */
    public function __construct($class, $batchSize = null)
    {
        $this->class = $class;

        if (!is_null($batchSize)) {
            $this->batchSize = $batchSize;
        }
    }

    /**
     * @inheritdoc
     */
    public function readBatch()
    {
        if (!extension_loaded('intl')) {
            //throw new Exception('Intl extension required in order to use this reader');
            return false;
        }

        $countries = Intl::getRegionBundle()->getCountryNames();
        $class = $this->class;

        $offset = $this->offset * $this->batchSize;
        $this->offset++;

        $isoCodes = $this->readFallbackData();

        $countries = array_slice($countries, $offset, $this->batchSize);
        if (!empty($countries)) {
            foreach ($countries as $iso2code => $countryName) {
                $result[] = new $class($countryName, $iso2code, isset($isoCodes[$iso2code]) ? $isoCodes[$iso2code] : $iso2code);
            }
        } else {
            $result = false;
        }

        return $result;
    }

    /**
     * Move it or delete when valid datasource will be found
     */
    public function readFallbackData()
    {
        $data = Yaml::parse(realpath(__DIR__ . '/../../' . self::FALLBACK_DATA));
        $isoCodes = array();

        foreach ($data as $country) {
            $isoCodes[ $country['iso2'] ] = $country['iso3'];
        }

        return $isoCodes;
    }
}
