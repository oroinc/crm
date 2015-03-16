<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Strategy\StrategyHelper;

use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;

use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\AddressBundle\Entity\AddressType;
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
        $newAddressTypes     = array_diff($remoteAddress->getTypeNames(), $localAddress->getTypeNames());
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
            'OroCRM\Bundle\MagentoBundle\Entity\Region'
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
     * @throws InvalidItemException
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
            [
                 'combinedCode' => $combinedCode
            ],
            $regionClass
        );
        if (!$region) {
            // Some region codes in magento are filled by region names
            $em      = $this->doctrineHelper->getEntityManager($countryClass);
            $country = $em->getReference($countryClass, $countryCode);
            $region  = $this->doctrineHelper->getEntityByCriteria(
                [
                     'country' => $country,
                     'name'    => $combinedCode
                ],
                $regionClass
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
                $regionClass
            );
        }

        return $region;
    }

    /**
     * @param AbstractAddress $address
     * @param string $countryCode
     * @param int|string|null $mageRegionId
     * @throws InvalidItemException
     */
    public function updateRegionByMagentoRegionId(AbstractAddress $address, $countryCode, $mageRegionId = null)
    {
        if (!empty($mageRegionId) && empty($this->mageRegionsCache[$mageRegionId]) && is_numeric($mageRegionId)) {
            $this->mageRegionsCache[$mageRegionId] = $this->findRegionByRegionId($mageRegionId);
        }

        if (!empty($this->mageRegionsCache[$mageRegionId])) {
            /** @var Region $mageRegion */
            $mageRegion = $this->mageRegionsCache[$mageRegionId];
            $combinedCode = $mageRegion->getCombinedCode();
            $regionCode = $mageRegion->getCode();

            if (!array_key_exists($combinedCode, $this->regionsCache)) {
                $this->regionsCache[$combinedCode] = $this->loadRegionByCode($combinedCode, $countryCode, $regionCode);
            }

            /**
             * no region found in system db for corresponding magento region, use region text
             */
            if (empty($this->regionsCache[$combinedCode])) {
                $address->setRegion(null);
                $address->setRegionText($mageRegion->getName());
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
}
