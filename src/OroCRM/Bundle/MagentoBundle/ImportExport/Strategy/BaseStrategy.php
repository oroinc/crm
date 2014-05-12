<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Strategy;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Oro\Bundle\AddressBundle\Entity\AbstractTypedAddress;
use Oro\Bundle\ImportExportBundle\Context\ContextAwareInterface;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Strategy\Import\ImportStrategyHelper;
use Oro\Bundle\ImportExportBundle\Strategy\StrategyInterface;
use Oro\Bundle\AddressBundle\Entity\Region;
use OroCRM\Bundle\MagentoBundle\ImportExport\Strategy\StrategyHelper\AddressImportHelper;
use OroCRM\Bundle\MagentoBundle\ImportExport\Strategy\StrategyHelper\DoctrineHelper;

abstract class BaseStrategy implements StrategyInterface, ContextAwareInterface
{
    /** @var ImportStrategyHelper */
    protected $strategyHelper;

    /** @var AddressImportHelper */
    protected $addressHelper;

    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /** @var ManagerRegistry */
    protected $managerRegistry;

    /** @var ContextInterface */
    protected $context;

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
        $this->doctrineHelper  = new DoctrineHelper($this->strategyHelper);
        $this->addressHelper   = new AddressImportHelper($this->doctrineHelper);
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
        return $this->doctrineHelper->findAndReplaceEntity($entity, $entityName, $criteria, $excludedProperties);
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
        return $this->doctrineHelper->getEntityOrNull($entity, $entityIdField, $entityClass);
    }

    /**
     * @param array         $criteria
     * @param object|string $entity object to get class from or class name
     *
     * @return object
     */
    protected function getEntityByCriteria(array $criteria, $entity)
    {
        return $this->doctrineHelper->getEntityByCriteria($criteria, $entity);
    }

    /**
     * @param $entityName
     *
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getEntityManager($entityName)
    {
        return $this->doctrineHelper->getEntityManager($entityName);
    }

    /**
     * @param string $entityName
     *
     * @return EntityRepository
     */
    protected function getEntityRepository($entityName)
    {
        return $this->doctrineHelper->getEntityRepository($entityName);
    }

    /**
     * @param $entity
     *
     * @return object
     */
    protected function merge($entity)
    {
        return $this->doctrineHelper->merge($entity);
    }

    /**
     * @param AbstractAddress $address
     * @param int             $mageRegionId
     *
     * @return $this
     */
    protected function updateAddressCountryRegion(AbstractAddress $address, $mageRegionId)
    {
        $this->addressHelper->updateAddressCountryRegion($address, $mageRegionId);

        return $this;
    }

    /**
     * @param string $combinedCode
     * @param string $countryCode
     * @param string $code
     *
     * @return Region
     */
    protected function loadRegionByCode($combinedCode, $countryCode, $code)
    {
        return $this->addressHelper->loadRegionByCode($combinedCode, $countryCode, $code);
    }

    /**
     * @param AbstractTypedAddress $address
     *
     * @return $this
     */
    protected function updateAddressTypes(AbstractTypedAddress $address)
    {
        $this->addressHelper->updateAddressTypes($address);

        return $this;
    }

    /**
     * @param AbstractAddress $address
     * @param string          $countryCode
     *
     * @return object|null
     */
    protected function getAddressCountryByCode(AbstractAddress $address, $countryCode)
    {
        return $this->addressHelper->getAddressCountryByCode($address, $countryCode);
    }
}
