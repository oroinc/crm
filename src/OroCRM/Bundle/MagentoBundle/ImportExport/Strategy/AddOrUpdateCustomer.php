<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Strategy;

use Doctrine\ORM\EntityRepository;

use Oro\Bundle\ImportExportBundle\Context\ContextAwareInterface;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Strategy\Import\ImportStrategyHelper;
use Oro\Bundle\ImportExportBundle\Strategy\StrategyInterface;

use OroCRM\Bundle\AccountBundle\ImportExport\Serializer\Normalizer\AccountNormalizer;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Entity\CustomerGroup;
use OroCRM\Bundle\MagentoBundle\Entity\Store;
use OroCRM\Bundle\MagentoBundle\Entity\Website;
use OroCRM\Bundle\MagentoBundle\ImportExport\Serializer\CustomerNormalizer;

class AddOrUpdateCustomer implements StrategyInterface, ContextAwareInterface
{
    const ENTITY_NAME = 'OroCRMMagentoBundle:Customer';
    const GROUP_ENTITY_NAME = 'OroCRMMagentoBundle:CustomerGroup';

    /** @var ImportStrategyHelper */
    protected $strategyHelper;

    /** @var ContextInterface */
    protected $importExportContext;

    /**
     * @param ImportStrategyHelper $strategyHelper
     */
    public function __construct(ImportStrategyHelper $strategyHelper)
    {
        $this->strategyHelper = $strategyHelper;
    }

    /**
     * Process item strategy
     *
     * @param mixed $entity
     * @return mixed|null
     */
    public function process($entity)
    {
        $entity = $this->findAndReplaceEntity($entity, self::ENTITY_NAME, 'originalId', ['id']);

        // update all related entities
        $this
            ->updateStoresAndGroup($entity)
            ->updateAccount($entity)
            ->updateContact($entity)
            ->updateAddresses($entity);

        // update owner for addresses, emails and phones
        $this->updateRelatedEntitiesOwner($entity);

        // validate and update context - increment counter or add validation error
        $entity = $this->validateAndUpdateContext($entity);

        return $entity;
    }

    /**
     * @param mixed $entity
     * @param $entityName
     * @param string $idFieldName
     * @param array $excludedProperties
     * @return Customer
     */
    protected function findAndReplaceEntity($entity, $entityName, $idFieldName = 'id', $excludedProperties = [])
    {
        $existingEntity = $this->getEntityOrNull($entity, $idFieldName, $entityName);

        if ($existingEntity) {
            $this->strategyHelper->importEntity($existingEntity, $entity, $excludedProperties);
            $entity = $existingEntity;
        } else {
            $entity->setId(null);
        }

        return $entity;
    }

    /**
     * @param Customer $entity
     * @return null|Customer
     */
    protected function validateAndUpdateContext(Customer $entity)
    {
        // validate contact
        $validationErrors = $this->strategyHelper->validateEntity($entity);
        if ($validationErrors) {
            $this->importExportContext->incrementErrorEntriesCount();
            $this->strategyHelper->addValidationErrors($validationErrors, $this->importExportContext);
            return null;
        }

        // increment context counter
        if ($entity->getId()) {
            $this->importExportContext->incrementReplaceCount();
        } else {
            $this->importExportContext->incrementAddCount();
        }

        return $entity;
    }

    /**
     * @param mixed $entity
     * @param $entityIdField
     * @param string $entityClass
     * @return Customer|null
     */
    protected function getEntityOrNull($entity, $entityIdField, $entityClass)
    {
        $existingEntity = null;
        $entityId = $entity->{'get'.ucfirst($entityIdField)}();

        if ($entityId) {
            $existingEntity = $this->getEntityRepository($entityClass)->findOneBy([$entityIdField => $entityId]);
        }

        return $existingEntity ?: null;
    }

    /**
     * @param string $entityName
     * @return EntityRepository
     */
    protected function getEntityRepository($entityName)
    {
        return $this->strategyHelper->getEntityManager($entityName)->getRepository($entityName);
    }

    /**
     * {@inheritDoc}
     */
    public function setImportExportContext(ContextInterface $importExportContext)
    {
        $this->importExportContext = $importExportContext;
    }

    /**
     * @param Customer $entity
     * @return $this
     */
    protected function updateStoresAndGroup(Customer $entity)
    {
        $self = $this;
        $findAndReplaceEntity = function ($entity, $type) use ($self) {
            // do not allow to change code/website name by imported entity
            return $self->findAndReplaceEntity($entity, $type, 'id', ['code', 'name']);
        };

        /** @var Website $websiteEntity */
        $websiteEntity = $findAndReplaceEntity($entity->getWebsite(), CustomerNormalizer::WEBSITE_TYPE);

        /** @var Store $storeEntity */
        $storeEntity = $findAndReplaceEntity($entity->getStore(), CustomerNormalizer::STORE_TYPE);
        $storeEntity->setWebsite($websiteEntity);

        /** @var CustomerGroup $groupEntity */
        $groupEntity = $findAndReplaceEntity($entity->getGroup(), CustomerNormalizer::GROUPS_TYPE);

        $entity
            ->setWebsite($websiteEntity)
            ->setStore($storeEntity)
            ->setGroup($groupEntity);

        return $this;
    }

    /**
     * @param Customer $entity
     * @return $this
     */
    protected function updateAddresses(Customer $entity)
    {
        // TODO: update addresses
        $entity->getContact()->resetAddresses([]);

        return $this;
    }

    /**
     * @param Customer $entity
     * @return $this
     */
    protected function updateAccount(Customer $entity)
    {
        $account = $entity->getAccount();

        $account = $this->findAndReplaceEntity($account, AccountNormalizer::ACCOUNT_TYPE, 'name', ['id']);

        $entity->setAccount($account);

        return $this;
    }

    /**
     * @param Customer $entity
     * @return $this
     */
    protected function updateContact(Customer $entity)
    {
        return $this;
    }

    /**
     * @param Customer $entity
     * @return $this
     */
    protected function updateRelatedEntitiesOwner(Customer $entity)
    {
        return $this;
    }
}
