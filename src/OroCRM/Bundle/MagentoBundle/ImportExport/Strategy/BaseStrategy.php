<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Strategy;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\UnitOfWork;

use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Oro\Bundle\AddressBundle\Entity\AbstractTypedAddress;
use Oro\Bundle\BatchBundle\Item\InvalidItemException;
use Oro\Bundle\ImportExportBundle\Context\ContextAwareInterface;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Strategy\Import\ImportStrategyHelper;
use Oro\Bundle\ImportExportBundle\Strategy\StrategyInterface;

abstract class BaseStrategy implements StrategyInterface, ContextAwareInterface
{
    /** @var ImportStrategyHelper */
    protected $strategyHelper;

    /** @var ContextInterface */
    protected $importExportContext;

    /** @var array */
    protected $regionsCache = [];

    /** @var array */
    protected $countriesCache = [];

    /** @var array */
    protected $mageRegionsCache = [];

    /**
     * @param ImportStrategyHelper $strategyHelper
     */
    public function __construct(ImportStrategyHelper $strategyHelper)
    {
        $this->strategyHelper = $strategyHelper;
    }

    /**
     * @param ContextInterface $importExportContext
     */
    public function setImportExportContext(ContextInterface $importExportContext)
    {
        $this->importExportContext = $importExportContext;
    }

    /**
     * @param mixed        $entity
     * @param string       $entityName
     * @param string|array $criteria
     * @param array        $excludedProperties
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
            $entity->setId(null);
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
            $this->importExportContext->incrementErrorEntriesCount();
            $this->strategyHelper->addValidationErrors($validationErrors, $this->importExportContext);

            return null;
        }

        // increment context counter
        if ($entity->getId()) {
            $this->importExportContext->incrementUpdateCount();
        } else {
            $this->importExportContext->incrementAddCount();
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
        $countryCode = $address->getCountry()->getIso2Code();

        $country = $this->getAddressCountryByCode($address, $countryCode);
        $address->setCountry($country);

        if (!empty($mageRegionId) && empty($this->mageRegionsCache[$mageRegionId])) {
            $this->mageRegionsCache[$mageRegionId] = $this->getEntityByCriteria(
                ['regionId' => $mageRegionId],
                'OroCRM\Bundle\MagentoBundle\Entity\Region'
            );
        }

        if (!empty($this->mageRegionsCache[$mageRegionId])) {
            $mageRegion   = $this->mageRegionsCache[$mageRegionId];
            $combinedCode = $mageRegion->getCombinedCode();

            $this->regionsCache[$combinedCode] = empty($this->regionsCache[$combinedCode]) ?
                $this->getEntityByCriteria(['combinedCode' => $combinedCode], 'Oro\Bundle\AddressBundle\Entity\Region')
                : $this->regionsCache[$combinedCode];

            // no region found in system db for corresponding magento region, use region text
            if (empty($this->regionsCache[$combinedCode])) {
                $address->setRegion(null);
            } else {
                $this->regionsCache[$combinedCode] = $this->merge($this->regionsCache[$combinedCode]);
                $address->setRegion($this->regionsCache[$combinedCode]);
                $address->setRegionText(null);
            }
        }

        return $this;
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
     * @return object
     */
    protected function getAddressCountryByCode(AbstractAddress $address, $countryCode)
    {
        $this->countriesCache[$countryCode] = empty($this->countriesCache[$countryCode])
            ? $this->findAndReplaceEntity(
                $address->getCountry(),
                'Oro\Bundle\AddressBundle\Entity\Country',
                'iso2Code',
                ['iso2Code', 'iso3Code', 'name']
            )
            : $this->merge($this->countriesCache[$countryCode]);

        if (empty($this->countriesCache[$countryCode])) {
            throw new InvalidItemException(sprintf('Unable to find country by code "%s"', $countryCode), []);
        }

        return $this->countriesCache[$countryCode];
    }
}
