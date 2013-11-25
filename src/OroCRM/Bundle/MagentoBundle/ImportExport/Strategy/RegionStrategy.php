<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Strategy;

use Doctrine\ORM\EntityRepository;

use Oro\Bundle\BatchBundle\Item\InvalidItemException;
use Oro\Bundle\ImportExportBundle\Context\ContextAwareInterface;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Strategy\Import\ImportStrategyHelper;
use Oro\Bundle\ImportExportBundle\Strategy\StrategyInterface;

use OroCRM\Bundle\AccountBundle\ImportExport\Serializer\Normalizer\AccountNormalizer;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Entity\CustomerGroup;
use OroCRM\Bundle\MagentoBundle\Entity\Region;
use OroCRM\Bundle\MagentoBundle\Entity\Store;
use OroCRM\Bundle\MagentoBundle\Entity\Website;
use OroCRM\Bundle\MagentoBundle\ImportExport\Serializer\CustomerNormalizer;

class RegionStrategy implements StrategyInterface, ContextAwareInterface
{
    const ENTITY_NAME = 'OroCRMMagentoBundle:Region';

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
        $entity = $this->findAndReplaceEntity($entity, self::ENTITY_NAME, 'combinedCode');

        // validate and update context - increment counter or add validation error
        $entity = $this->validateAndUpdateContext($entity);

        return $entity;
    }

    /**
     * @param mixed $entity
     * @param string $entityName
     * @param string $idFieldName
     * @param array $excludedProperties
     * @return Region
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
     * @param Region $entity
     * @return null|Region
     */
    protected function validateAndUpdateContext(Region $entity)
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
     * @param string $entityIdField
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
        // do not allow to change code/website name by imported entity
        /** @var Website $websiteEntity */
        $websiteEntity = $this->findAndReplaceEntity(
            $entity->getWebsite(),
            CustomerNormalizer::WEBSITE_TYPE,
            'code',
            ['code', 'name']
        );

        /** @var Store $storeEntity */
        $storeEntity = $this->findAndReplaceEntity(
            $entity->getStore(),
            CustomerNormalizer::STORE_TYPE,
            'code',
            ['code', 'name']
        );
        $storeEntity->setWebsite($websiteEntity);

        /** @var CustomerGroup $groupEntity */
        $groupEntity = $this->findAndReplaceEntity(
            $entity->getGroup(),
            CustomerNormalizer::GROUPS_TYPE,
            'name',
            ['code', 'name']
        );

        $entity
            ->setWebsite($websiteEntity)
            ->setStore($storeEntity)
            ->setGroup($groupEntity);

        return $this;
    }

    /**
     * @param Customer $entity
     * @throws \Oro\Bundle\BatchBundle\Item\InvalidItemException
     * @return $this
     */
    protected function updateAddresses(Customer $entity)
    {
        $addresses = $entity->getContact()->getAddresses();

        foreach ($addresses as $address) {
            $countryCode = $address->getCountry()->getIso2Code();
            $this->regionsCache[$countryCode] = empty($this->regionsCache[$countryCode]) ?
                $this->findAndReplaceEntity(
                    $address->getCountry(),
                    'Oro\Bundle\AddressBundle\Entity\Country',
                    'iso2Code',
                    ['iso2Code', 'iso3Code', 'name']
                ) :
                $this->regionsCache[$countryCode];

            $regionCode = $address->getRegion()->getCombinedCode();
            $this->regionsCache[$regionCode] = empty($this->regionsCache[$regionCode]) ?
                $this->getEntityOrNull($address->getRegion(), 'combinedCode', 'Oro\Bundle\AddressBundle\Entity\Region'):
                $this->regionsCache[$regionCode];

            if (empty($this->regionsCache[$regionCode])) {
                throw new InvalidItemException(
                    sprintf("Cannot find '%s' region for '%s' country", $regionCode, $countryCode),
                    [$entity]
                );
            }

            $address->setCountry($this->regionsCache[$countryCode])
                ->setRegion($this->regionsCache[$regionCode]);
        }


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
