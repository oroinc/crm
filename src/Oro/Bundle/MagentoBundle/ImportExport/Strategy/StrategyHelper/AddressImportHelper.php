<?php

namespace Oro\Bundle\MagentoBundle\ImportExport\Strategy\StrategyHelper;

use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\AddressType;
use Oro\Bundle\AddressBundle\Entity\Region as BAPRegion;
use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Oro\Bundle\AddressBundle\Entity\AbstractTypedAddress;
use Oro\Bundle\MagentoBundle\Entity\Region;

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

    /** @var AddressType[] */
    protected $addressTypesCache = [];

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
     */
    public function updateAddressCountryRegion(AbstractAddress $address, $mageRegionId)
    {
        if (!$address->getCountry()) {
            return;
        }

        $countryCode = $address->getCountry()->getIso2Code();
        $country = $this->getAddressCountryByCode($address, $countryCode);
        $address->setCountry($country);
        if (!$country) {
            return;
        }

        $this->updateRegionByMagentoRegionId($address, $countryCode, $mageRegionId);
    }

    /**
     * @param AbstractTypedAddress $address
     */
    public function updateAddressTypes(AbstractTypedAddress $address)
    {
        $addressTypes = $address->getTypes();
        foreach ($addressTypes as $index => $type) {
            $addressTypes->set($index, $this->updateAddressType($type->getName()));
        }
    }

    /**
     * @param AbstractTypedAddress $localAddress
     * @param AbstractTypedAddress $remoteAddress
     */
    public function mergeAddressTypes(AbstractTypedAddress $localAddress, AbstractTypedAddress $remoteAddress)
    {
        $newAddressTypes = array_diff($remoteAddress->getTypeNames(), $localAddress->getTypeNames());
        $deletedAddressTypes = array_diff($localAddress->getTypeNames(), $remoteAddress->getTypeNames());

        foreach ($deletedAddressTypes as $addressType) {
            $localAddress->removeType($localAddress->getTypeByName($addressType));
        }
        foreach ($newAddressTypes as $addressType) {
            $localAddress->addType($remoteAddress->getTypeByName($addressType));
        }
    }

    /**
     * @param $mageRegionId
     *
     * @return object
     */
    public function findRegionByRegionId($mageRegionId)
    {
        return $this->doctrineHelper->getEntityByCriteria(
            ['regionId' => $mageRegionId],
            Region::class
        );
    }

    /**
     * @param string $name
     *
     * @return AddressType
     */
    protected function updateAddressType($name)
    {
        $typeClass = 'OroAddressBundle:AddressType';

        if (empty($this->addressTypesCache[$name])
            || !$this->doctrineHelper->getEntityManager($typeClass)
                ->getUnitOfWork()->isInIdentityMap($this->addressTypesCache[$name])
        ) {
            $this->addressTypesCache[$name] = $this->doctrineHelper->getEntityRepository($typeClass)->find($name);
        }

        return $this->addressTypesCache[$name];
    }

    /**
     * @param AbstractAddress $address
     * @param string          $countryCode
     *
     * @return Country|null
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
            $country = $this->doctrineHelper->findAndReplaceEntity(
                $address->getCountry(),
                Country::class,
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
     * @return BAPRegion|null
     */
    public function loadRegionByCode($combinedCode, $countryCode, $code)
    {
        // Simply search region by combinedCode
        $region = $this->doctrineHelper->getEntityByCriteria(
            [
                'combinedCode' => $combinedCode
            ],
            BAPRegion::class
        );
        if (!$region) {
            // Some region codes in magento are filled by region names
            $em = $this->doctrineHelper->getEntityManager(Country::class);
            $country = $em->getReference(Country::class, $countryCode);
            $region = $this->doctrineHelper->getEntityByCriteria(
                [
                    'country' => $country,
                    'name' => $combinedCode
                ],
                BAPRegion::class
            );
        }
        if (!$region) {
            // Some numeric regions codes may be padded by 0 in ISO format and not padded in magento
            // As example FR-1 in magento and FR-01 in ISO
            $region = $this->doctrineHelper->getEntityByCriteria(
                [
                    'combinedCode' =>
                        BAPRegion::getRegionCombinedCode(
                            $countryCode,
                            str_pad($code, 2, '0', STR_PAD_LEFT)
                        )
                ],
                BAPRegion::class
            );
        }

        return $region;
    }

    /**
     * @param AbstractAddress $address
     * @param string          $countryCode
     * @param int|string|null $mageRegionId
     */
    public function updateRegionByMagentoRegionId(AbstractAddress $address, $countryCode, $mageRegionId = null)
    {
        $this->updateRegionByMagentoRegionIdOrUnsetNonSystemRegionOnly($address, $countryCode, $mageRegionId);
    }

    /**
     * @param AbstractAddress $address
     * @param string          $countryCode
     * @param null            $mageRegionId
     * @param bool            $unsetNonSystemRegionOnly
     *
     * @deprecated Since 2.0, will be removed after 2.3 and all code will move to `updateRegionByMagentoRegionId`
     */
    public function updateRegionByMagentoRegionIdOrUnsetNonSystemRegionOnly(
        AbstractAddress $address,
        $countryCode,
        $mageRegionId = null,
        $unsetNonSystemRegionOnly = false
    ) {
        $magentoRegion = $this->getMagentoRegionByRegionId($mageRegionId);
        $region = $this->getSystemRegion($countryCode, $magentoRegion);

        /**
         * no region found in system db for corresponding magento region, use region text
         */
        if (null === $region) {
            $address->setRegion(null);
            if ($magentoRegion instanceof Region) {
                $address->setRegionText($magentoRegion->getName());
            }
        } elseif (!$unsetNonSystemRegionOnly) {
            /**
             * @var $region BAPRegion
             */
            $region = $this->doctrineHelper->merge($region);
            $address->setRegion($region);
            $address->setRegionText(null);
        }
    }

    /**
     * @param string          $countryCode
     * @param Region|null     $magentoRegion
     *
     * @return bool|mixed
     */
    protected function getSystemRegion($countryCode, $magentoRegion)
    {
        if (!empty($magentoRegion)) {
            /** @var Region $mageRegion */
            $combinedCode = $magentoRegion->getCombinedCode();
            $regionCode = $magentoRegion->getCode();

            if (!array_key_exists($combinedCode, $this->regionsCache)) {
                $this->regionsCache[$combinedCode] = $this->loadRegionByCode($combinedCode, $countryCode, $regionCode);
            }

            return $this->regionsCache[$combinedCode];
        }

        // unable to find corresponding BAPRegion
        // it's correct case for UK, DE addresses, if country present
        return null;
    }

    /**
     * @param $mageRegionId
     *
     * @return Region|null
     */
    protected function getMagentoRegionByRegionId($mageRegionId)
    {
        if (is_numeric($mageRegionId) && empty($this->mageRegionsCache[$mageRegionId])) {
            $this->mageRegionsCache[$mageRegionId] = $this->findRegionByRegionId($mageRegionId);
        }

        if (array_key_exists($mageRegionId, $this->mageRegionsCache)) {
            return $this->mageRegionsCache[$mageRegionId];
        }

        return null;
    }
}
