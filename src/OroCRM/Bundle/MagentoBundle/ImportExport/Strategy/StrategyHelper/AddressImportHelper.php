<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Strategy\StrategyHelper;

use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;

use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\Region as BAPRegion;
use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Oro\Bundle\AddressBundle\Entity\AbstractTypedAddress;

use OroCRM\Bundle\MagentoBundle\Entity\Region;

class AddressImportHelper
{
    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /** @var array */
    protected $regionsCache = [];

    /** @var array */
    protected $countriesCache = [];

    /** @var array */
    protected $mageRegionsCache = [];

    /**
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(DoctrineHelper $doctrineHelper)
    {
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * @param AbstractAddress $address
     * @param int             $mageRegionId
     *
     * @throws InvalidItemException
     */
    public function updateAddressCountryRegion(AbstractAddress $address, $mageRegionId)
    {
        if (!$address->getCountry()) {
            return;
        }

        $countryCode = $address->getCountry()->getIso2Code();
        $country     = $this->getAddressCountryByCode($address, $countryCode);
        $address->setCountry($country);
        if (!$country) {
            return;
        }

        if (!empty($mageRegionId) && empty($this->mageRegionsCache[$mageRegionId])) {
            $this->mageRegionsCache[$mageRegionId] = $this->doctrineHelper->getEntityByCriteria(
                ['regionId' => $mageRegionId],
                'OroCRM\Bundle\MagentoBundle\Entity\Region'
            );
        }

        if (!empty($this->mageRegionsCache[$mageRegionId])) {
            /** @var Region $mageRegion */
            $mageRegion   = $this->mageRegionsCache[$mageRegionId];
            $combinedCode = $mageRegion->getCombinedCode();
            $regionCode   = $mageRegion->getCode();

            if (!array_key_exists($combinedCode, $this->regionsCache)) {
                $this->regionsCache[$combinedCode] = $this->loadRegionByCode($combinedCode, $countryCode, $regionCode);
            }

            // no region found in system db for corresponding magento region, use region text
            if (empty($this->regionsCache[$combinedCode])) {
                $address->setRegion(null);
            } else {
                $this->regionsCache[$combinedCode] = $this->doctrineHelper->merge($this->regionsCache[$combinedCode]);
                $address->setRegion($this->regionsCache[$combinedCode]);
                $address->setRegionText(null);
            }
        } elseif ($address->getRegionText() || $address->getCountry()) {
            $address->setRegion(null);
            // unable to find corresponding region and region text is empty,
            // it's correct case for UK addresses, if country present
        } else {
            throw new InvalidItemException('Unable to handle region for address', [$address]);
        }
    }

    /**
     * @param AbstractTypedAddress $address
     */
    public function updateAddressTypes(AbstractTypedAddress $address)
    {
        // update address type
        $types = $address->getTypeNames();
        if (empty($types)) {
            return;
        }

        $address->getTypes()->clear();
        $loadedTypes = $this->doctrineHelper->getEntityRepository('OroAddressBundle:AddressType')
            ->findBy(['name' => $types]);

        foreach ($loadedTypes as $type) {
            $address->addType($type);
        }
    }

    /**
     * @param AbstractAddress $address
     * @param string          $countryCode
     *
     * @throws InvalidItemException
     * @return object|null
     */
    public function getAddressCountryByCode(AbstractAddress $address, $countryCode)
    {
        if (!$address->getCountry()) {
            return null;
        }

        if (array_key_exists($countryCode, $this->countriesCache)) {
            if (!empty($this->countriesCache[$countryCode])) {
                $this->countriesCache[$countryCode] = $this->doctrineHelper->merge($this->countriesCache[$countryCode]);
            }
        } else {
            /** @var Country $country */
            $country                            = $this->doctrineHelper->findAndReplaceEntity(
                $address->getCountry(),
                'Oro\Bundle\AddressBundle\Entity\Country',
                'iso2Code',
                ['iso2Code', 'iso3Code', 'name']
            );
            $this->countriesCache[$countryCode] = $country->getIso2Code() ? $country : null;
        }

        return $this->countriesCache[$countryCode];
    }


    /**
     * @param string $combinedCode
     * @param string $countryCode
     * @param string $code
     *
     * @return BAPRegion
     */
    public function loadRegionByCode($combinedCode, $countryCode, $code)
    {
        $regionClass  = 'Oro\Bundle\AddressBundle\Entity\Region';
        $countryClass = 'Oro\Bundle\AddressBundle\Entity\Country';

        // Simply search region by combinedCode
        $region = $this->doctrineHelper->getEntityByCriteria(
            array(
                'combinedCode' => $combinedCode
            ),
            $regionClass
        );
        if (!$region) {
            // Some region codes in magento are filled by region names
            $em      = $this->doctrineHelper->getEntityManager($countryClass);
            $country = $em->getReference($countryClass, $countryCode);
            $region  = $this->doctrineHelper->getEntityByCriteria(
                array(
                    'country' => $country,
                    'name'    => $combinedCode
                ),
                $regionClass
            );
        }
        if (!$region) {
            // Some numeric regions codes may be padded by 0 in ISO format and not padded in magento
            // As example FR-1 in magento and FR-01 in ISO
            $region = $this->doctrineHelper->getEntityByCriteria(
                array(
                    'combinedCode' =>
                        BAPRegion::getRegionCombinedCode(
                            $countryCode,
                            str_pad($code, 2, '0', STR_PAD_LEFT)
                        )
                ),
                $regionClass
            );
        }

        return $region;
    }
}
