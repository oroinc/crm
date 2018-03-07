<?php

namespace Oro\Bundle\MagentoBundle\ImportExport\Strategy\StrategyHelper;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\UnitOfWork;
use Oro\Bundle\ImportExportBundle\Strategy\Import\ImportStrategyHelper;
use Symfony\Component\Security\Acl\Util\ClassUtils;

class DoctrineHelper
{
    /** @var ImportStrategyHelper */
    protected $strategyHelper;

    public function __construct(ImportStrategyHelper $strategyHelper)
    {
        $this->strategyHelper = $strategyHelper;
    }

    /**
     * @param mixed        $entity             New entity
     * @param string       $entityName         Class name
     * @param string|array $criteria           Fieldname to find existing entity
     * @param array        $excludedProperties Excluded properties
     *
     * @return mixed
     */
    public function findAndReplaceEntity($entity, $entityName, $criteria = 'id', $excludedProperties = [])
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
            $metadata = $this->getEntityManager($entityName)
                ->getClassMetadata($entityName);

            $identifier   = $metadata->getSingleIdentifierFieldName();
            $setterMethod = 'set' . ucfirst($identifier);
            if (method_exists($entity, $setterMethod)) {
                $entity->$setterMethod(null);
            } elseif (property_exists($entity, $identifier)) {
                $reflection = new \ReflectionProperty(ClassUtils::getRealClass($entity), $identifier);
                $reflection->setAccessible(true);
                $reflection->setValue($entity, null);
            }
        }

        return $entity;
    }

    /**
     * @param mixed       $entity
     * @param string      $entityIdField
     * @param string|null $entityClass
     *
     * @return object|null
     */
    public function getEntityOrNull($entity, $entityIdField, $entityClass = null)
    {
        $existingEntity = null;
        $entityId       = $entity->{'get' . ucfirst($entityIdField)}();

        if (!$entityClass) {
            $entityClass = ClassUtils::getRealClass($entity);
        }

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
    public function getEntityByCriteria(array $criteria, $entity)
    {
        $entityClass = ClassUtils::getRealClass($entity);

        return $this->getEntityRepository($entityClass)->findOneBy($criteria);
    }

    /**
     * @param $entityName
     *
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager($entityName)
    {
        return $this->strategyHelper->getEntityManager($entityName);
    }

    /**
     * @param string $entityName
     *
     * @return EntityRepository
     */
    public function getEntityRepository($entityName)
    {
        return $this->strategyHelper->getEntityManager($entityName)->getRepository($entityName);
    }

    /**
     * @param $entity
     *
     * @return object
     */
    public function merge($entity)
    {
        /*
         * Reload entity instead merge due to strange behavior with spl_object_hash
         * EntityManager#find has own cache, so query will be performed only once per batch (until EntityManager#clear)
         */
        $cn = ClassUtils::getRealClass($entity);
        $em = $this->getEntityManager($cn);
        if ($em->getUnitOfWork()->getEntityState($entity) !== UnitOfWork::STATE_MANAGED) {
            $id = $em->getClassMetadata($cn)->getIdentifierValues($entity);
            if ($id) {
                // even if ID exists, it's possible to be after rollback
                $entity = $em->find($cn, $id) ?: $entity;
            }
        }

        return $entity;
    }
}
