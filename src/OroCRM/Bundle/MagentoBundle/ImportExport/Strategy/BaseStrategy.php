<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Strategy;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\UnitOfWork;

use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Oro\Bundle\AddressBundle\Entity\AbstractTypedAddress;
use Oro\Bundle\AddressBundle\Entity\Country;
use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use Oro\Bundle\ImportExportBundle\Context\ContextAwareInterface;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Strategy\Import\ImportStrategyHelper;
use Oro\Bundle\ImportExportBundle\Strategy\StrategyInterface;
use OroCRM\Bundle\MagentoBundle\Entity\Region;
use Oro\Bundle\AddressBundle\Entity\Region as BAPRegion;

abstract class BaseStrategy implements StrategyInterface, ContextAwareInterface
{
    /** @var ImportStrategyHelper */
    protected $strategyHelper;

    /** @var ManagerRegistry */
    protected $managerRegistry;

    /** @var ContextInterface */
    protected $context;

    /** @var array */
    protected $regionsCache = [];

    /** @var array */
    protected $countriesCache = [];

    /** @var array */
    protected $mageRegionsCache = [];

    /**
     * @param ImportStrategyHelper $strategyHelper
     * @param ManagerRegistry      $managerRegistry
     */
    public function __construct(
        ImportStrategyHelper $strategyHelper,
        ManagerRegistry $managerRegistry
    ) {
        $this->strategyHelper  = $strategyHelper;
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * @param ContextInterface $context
     */
    public function setImportExportContext(ContextInterface $context)
    {
        $this->context = $context;
    }

    /**
     * @param mixed        $entity             New entity
     * @param string       $entityName         Class name
     * @param string|array $criteria           Fieldname to find existing entity
     * @param array        $excludedProperties Excluded properties
     *
     * @return mixed
     */
    protected function findAndReplaceEntity($entity, $entityName, $criteria = 'id', $excludedProperties = [])
    {
        if (is_array($criteria)) {
            $existingEntity = $this->getEntityByCriteria($criteria, $entity);
        } else {
            $existingEntity = $this->getEntityOrNull($entity, $criteria, $entityName);
        }

        if ($existingEntity) {
            $this->strategyHelper->importEntity($existingEntity, $entity, $excludedProperties);
            $entity = $existingEntity;
        } else {
            /* @var ClassMetadataInfo $metadata */
            $metadata = $this
                ->managerRegistry
                ->getManagerForClass($entityName)
                ->getClassMetadata($entityName);

            $identifier   = $metadata->getSingleIdentifierFieldName();
            $setterMethod = 'set' . ucfirst($identifier);
            if (method_exists($entity, $setterMethod)) {
                $entity->$setterMethod(null);
            } elseif (property_exists($entity, $identifier)) {
                $reflection = new \ReflectionProperty(ClassUtils::getClass($entity), $identifier);
                $reflection->setAccessible(true);
                $reflection->setValue($entity, null);
            }
        }

        return $entity;
    }

    /**
     * @param object $entity
     *
     * @return null|object
     */
    protected function validateAndUpdateContext($entity)
    {
        // validate entity
        $validationErrors = $this->strategyHelper->validateEntity($entity);
        if ($validationErrors) {
            $this->context->incrementErrorEntriesCount();
            $this->strategyHelper->addValidationErrors($validationErrors, $this->context);

            return null;
        }

        // increment context counter
        if ($entity->getId()) {
            $this->context->incrementUpdateCount();
        } else {
            $this->context->incrementAddCount();
        }

        return $entity;
    }

    /**
     * @param mixed  $entity
     * @param string $entityIdField
     * @param string $entityClass
     *
     * @return object|null
     */
    protected function getEntityOrNull($entity, $entityIdField, $entityClass)
    {
        $existingEntity = null;
        $entityId       = $entity->{'get' . ucfirst($entityIdField)}();

        if ($entityId) {
            $existingEntity = $this->getEntityByCriteria([$entityIdField => $entityId], $entityClass);
        }

        return $existingEntity ? : null;
    }

    /**
     * @param array         $criteria
     * @param object|string $entity object to get class from or class name
     *
     * @return object
     */
    protected function getEntityByCriteria(array $criteria, $entity)
    {
        if (is_object($entity)) {
            $entityClass = ClassUtils::getClass($entity);
        } else {
            $entityClass = $entity;
        }

        return $this->getEntityRepository($entityClass)->findOneBy($criteria);
    }

    /**
     * @param $entityName
     *
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getEntityManager($entityName)
    {
        return $this->strategyHelper->getEntityManager($entityName);
    }

    /**
     * @param string $entityName
     *
     * @return EntityRepository
     */
    protected function getEntityRepository($entityName)
    {
        return $this->strategyHelper->getEntityManager($entityName)->getRepository($entityName);
    }

    /**
     * @param $entity
     *
     * @return object
     */
    protected function merge($entity)
    {
        $em = $this->getEntityManager(ClassUtils::getClass($entity));
        if ($em->getUnitOfWork()->getEntityState($entity) !== UnitOfWork::STATE_MANAGED) {
            $entity = $em->merge($entity);
        }

        return $entity;
    }

    /**
     * @param AbstractAddress $address
     * @param int             $mageRegionId
     *
     * @return $this
     *
     * @throws InvalidItemException
     */
    protected function updateAddressCountryRegion(AbstractAddress $address, $mageRegionId)
    {
        if (!$address->getCountry()) {
            return $this;
        }

        $countryCode = $address->getCountry()->getIso2Code();
        $country = $this->getAddressCountryByCode($address, $countryCode);
        $address->setCountry($country);
        if (!$country) {
            return $this;
        }

        if (!empty($mageRegionId) && empty($this->mageRegionsCache[$mageRegionId])) {
            $this->mageRegionsCache[$mageRegionId] = $this->getEntityByCriteria(
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
                $this->regionsCache[$combinedCode] = $this->merge($this->regionsCache[$combinedCode]);
                $address->setRegion($this->regionsCache[$combinedCode]);
                $address->setRegionText(null);
            }
        } elseif ($address->getRegionText()) {
            $address->setRegion(null);
        } elseif ($address->getCountry()) {
            // unable to find corresponding region and region text is empty,
            // it's correct case for UK addresses, if country present
        } else {
            throw new InvalidItemException('Unable to handle region for address', [$address]);
        }

        return $this;
    }

    /**
     * @param string $combinedCode
     * @param string $countryCode
     * @param string $code
     * @return BAPRegion
     */
    protected function loadRegionByCode($combinedCode, $countryCode, $code)
    {
        $regionClass = 'Oro\Bundle\AddressBundle\Entity\Region';
        $countryClass = 'Oro\Bundle\AddressBundle\Entity\Country';

        // Simply search region by combinedCode
        $region = $this->getEntityByCriteria(
            array(
                'combinedCode' => $combinedCode
            ),
            $regionClass
        );
        if (!$region) {
            // Some region codes in magento are filled by region names
            $entityManager = $this->getEntityManager($countryClass);
            $country = $entityManager->getReference($countryClass, $countryCode);
            $region = $this->getEntityByCriteria(
                array(
                    'country' => $country,
                    'name' => $combinedCode
                ),
                $regionClass
            );
        }
        if (!$region) {
            // Some numeric regions codes may be padded by 0 in ISO format and not padded in magento
            // As example FR-1 in magento and FR-01 in ISO
            $region = $this->getEntityByCriteria(
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

    /**
     * @param AbstractTypedAddress $address
     *
     * @return $this
     */
    protected function updateAddressTypes(AbstractTypedAddress $address)
    {
        // update address type
        $types = $address->getTypeNames();
        if (empty($types)) {
            return $this;
        }

        $address->getTypes()->clear();
        $loadedTypes = $this->getEntityRepository('OroAddressBundle:AddressType')->findBy(['name' => $types]);

        foreach ($loadedTypes as $type) {
            $address->addType($type);
        }

        return $this;
    }

    /**
     * @param AbstractAddress $address
     * @param string          $countryCode
     *
     * @throws InvalidItemException
     * @return object|null
     */
    protected function getAddressCountryByCode(AbstractAddress $address, $countryCode)
    {
        if (!$address->getCountry()) {
            return null;
        }

        if (array_key_exists($countryCode, $this->countriesCache)) {
            if (!empty($this->countriesCache[$countryCode])) {
                $this->countriesCache[$countryCode] = $this->merge($this->countriesCache[$countryCode]);
            }
        } else {
            /** @var Country $country */
            $country = $this->findAndReplaceEntity(
                $address->getCountry(),
                'Oro\Bundle\AddressBundle\Entity\Country',
                'iso2Code',
                ['iso2Code', 'iso3Code', 'name']
            );
            $this->countriesCache[$countryCode] = $country->getIso2Code() ? $country : null;
        }

        return $this->countriesCache[$countryCode];
    }
}
