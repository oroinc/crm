<?php

namespace OroCRM\Bundle\MagentoBundle\Provider;

use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Repository\CountryRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class Iso2CodeProvider
{
    /**
     * @var RegistryInterface
     */
    private $registry;

    /**
     * @var array
     */
    private $iso2Mapping = [];

    /**
     * @var array
     */
    private $iso3Mapping = [];

    /**
     * @var array
     */
    private $nameMapping = [];

    /**
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @param string $countryId
     * @return string|null
     */
    public function getIso2CodeByCountryId($countryId)
    {
        $this->ensureMappingsLoaded();

        if (isset($this->iso2Mapping[$countryId])) {
            return $this->iso2Mapping[$countryId];
        }

        if (isset($this->iso3Mapping[$countryId])) {
            return $this->iso3Mapping[$countryId];
        }

        if (isset($this->nameMapping[$countryId])) {
            return $this->nameMapping[$countryId];
        }

        return null;
    }

    private function ensureMappingsLoaded()
    {
        if ($this->iso2Mapping && $this->iso3Mapping && $this->nameMapping) {
            return;
        }

        /** @var CountryRepository $countryRepository */
        $countryRepository = $this->registry->getRepository(Country::class);
        foreach ($countryRepository->getAllCountryNamesArray() as $country) {
            $this->iso2Mapping[$country['iso2Code']] = $country['iso2Code'];
            $this->iso3Mapping[$country['iso3Code']] = $country['iso2Code'];
            $this->nameMapping[$country['name']] = $country['iso2Code'];
        }
    }
}
