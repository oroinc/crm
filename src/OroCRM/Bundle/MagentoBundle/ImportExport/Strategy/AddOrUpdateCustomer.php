<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Strategy;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityRepository;

use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Oro\Bundle\AddressBundle\Entity\AbstractTypedAddress;
use Oro\Bundle\BatchBundle\Item\InvalidItemException;
use Oro\Bundle\ImportExportBundle\Context\ContextAwareInterface;
use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Strategy\Import\ImportStrategyHelper;
use Oro\Bundle\ImportExportBundle\Strategy\StrategyInterface;

use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\AccountBundle\ImportExport\Serializer\Normalizer\AccountNormalizer;
use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\ContactBundle\Entity\ContactAddress;
use OroCRM\Bundle\ContactBundle\ImportExport\Serializer\Normalizer\ContactNormalizer;
use OroCRM\Bundle\MagentoBundle\Entity\AddressRelation;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Entity\CustomerGroup;
use OroCRM\Bundle\MagentoBundle\Entity\Store;
use OroCRM\Bundle\MagentoBundle\Entity\Website;
use OroCRM\Bundle\MagentoBundle\ImportExport\Serializer\CustomerNormalizer;

class AddOrUpdateCustomer implements StrategyInterface, ContextAwareInterface
{
    const ENTITY_NAME = 'OroCRMMagentoBundle:Customer';
    const GROUP_ENTITY_NAME = 'OroCRMMagentoBundle:CustomerGroup';
    const ADDRESS_RELATION_ENTITY = 'OroCRMMagentoBundle:AddressRelation';

    /** @var ImportStrategyHelper */
    protected $strategyHelper;

    /** @var ContextInterface */
    protected $importExportContext;

    /** @var array */
    protected $regionsCache = [];

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
     * Process item strategy
     *
     * @param mixed $entity
     * @throws \Exception
     * @return mixed|null
     */
    public function process($entity)
    {
        $newEntity = $this->findAndReplaceEntity(
            $entity,
            self::ENTITY_NAME,
            'originalId',
            ['id', 'contact', 'account']
        );

        // fill existing ids
        if ($newEntity->getContact()) {
            $originalContactId = $newEntity->getContact()->getId();
            $entity->getContact()->setId($originalContactId);
        }
        if ($newEntity->getAccount()) {
            $originalAccountId = $newEntity->getAccount()->getId();
            $entity->getAccount()->setId($originalAccountId);
        }
        $newEntity
            ->setContact($entity->getContact())
            ->setAccount($entity->getAccount());

        // update all related entities
        $this->updateStoresAndGroup($entity)
             ->updateContact($entity)
             ->updateAccount($entity);

        $entity->getContact()->addAccount($entity->getAccount());
        $entity->getAccount()->setDefaultContact($entity->getContact());

        // update owner for addresses, emails and phones
        $this->updateRelatedEntitiesOwner($entity);

        // validate and update context - increment counter or add validation error
        $entity = $this->validateAndUpdateContext($entity);

        return $entity;
    }

    /**
     * @param mixed $entity
     * @param string $entityName
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
        return $this->getEntityManager($entityName)->getRepository($entityName);
    }

    /**
     * @param $entityName
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getEntityManager($entityName)
    {
        return $this->strategyHelper->getEntityManager($entityName);
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
            ['id', 'code', 'name']
        );

        /** @var Store $storeEntity */
        $storeEntity = $this->findAndReplaceEntity(
            $entity->getStore(),
            CustomerNormalizer::STORE_TYPE,
            'code',
            ['id', 'code', 'name']
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
     * @param AbstractAddress $address
     * @param Customer $entity
     * @throws InvalidItemException
     */
    protected function updateAddressCountryRegion(AbstractAddress $address, Customer $entity)
    {
        $countryCode = $address->getCountry()->getIso2Code();

        // country cache
        $this->regionsCache[$countryCode] = empty($this->regionsCache[$countryCode]) ?
            $this->findAndReplaceEntity(
                $address->getCountry(),
                'Oro\Bundle\AddressBundle\Entity\Country',
                'iso2Code',
                ['iso2Code', 'iso3Code', 'name']
            ) :
            $this->regionsCache[$countryCode];

        // get region by Magento code (region_id)
        $regionCode = $address->getRegion()->getCode();

        if (empty($this->mageRegionsCache[$regionCode])) {
            $this->mageRegionsCache[$regionCode] = $this->getEntityRepository(
                'OroCRM\Bundle\MagentoBundle\Entity\Region'
            )
            ->findOneBy(['region_id' => $regionCode]);
        }

        $mageRegion = $this->mageRegionsCache[$regionCode];
        $combinedCode = $mageRegion->getCombinedCode();

        // set ISO combined code
        $address->getRegion()->setCombinedCode($combinedCode);

        // get region
        $this->regionsCache[$combinedCode] = empty($this->regionsCache[$combinedCode]) ?
            $this->getEntityOrNull($address->getRegion(), 'combinedCode', 'Oro\Bundle\AddressBundle\Entity\Region'):
            $this->regionsCache[$combinedCode];

        if (empty($this->regionsCache[$combinedCode])) {
            throw new InvalidItemException(
                sprintf("Cannot find '%s' region for '%s' country", $combinedCode, $countryCode),
                [$entity]
            );
        }

        $address->setCountry($this->regionsCache[$countryCode])
            ->setRegion($this->regionsCache[$combinedCode]);
    }


    /**
     * @param Customer $entity
     * @return $this
     */
    protected function updateAccount(Customer $entity)
    {
        /** @var Account $account */
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
        /** @var Contact $contact */
        $contact = $entity->getContact();

        /** @var Contact $newContact */
        $newContact = $this->findAndReplaceEntity($contact, ContactNormalizer::CONTACT_TYPE, 'id', ['id', 'addresses']);

        $existingAddressIds = [];
        $existingAddressEntities = [];
        foreach ($newContact->getAddresses() as $existingAddress) {
            $existingAddressIds[] = $existingAddress->getId();
            $existingAddressEntities[$existingAddress->getId()] = $existingAddress;
        }

        $originAddressIds = $this->getOriginAddressesIds($existingAddressIds);

        // loop by imported addresses, update existing, add new
        foreach ($contact->getAddresses() as $address) {
            $originAddressId = $address->getId();
            $existingAddressId = empty($originAddressIds[$originAddressId]) ?
                null : $originAddressIds[$originAddressId];

            if (!empty($existingAddressId) && isset($existingAddressEntities[$existingAddressId])) {
                $newContact->removeAddress($existingAddressEntities[$existingAddressId]);

                // so we have new data for existing address
                $this->strategyHelper->importEntity($existingAddressEntities[$existingAddressId], $address, ['id']);
                $address = $existingAddressEntities[$existingAddressId];
            } else {
                // it's not existing address
                $address->setId(null);
            }

            $this->updateAddressCountryRegion($address, $entity);

            // update address type
            $types = $address->getTypeNames();
            $address->getTypes()->clear();
            $loadedTypes = $this->getEntityRepository('OroAddressBundle:AddressType')
                ->findBy(['name' => $types]);
            foreach ($loadedTypes as $type) {
                $address->addType($type);
            }

            $newContact->addAddress($address);

            if (!in_array($originAddressId, array_keys($originAddressIds))) {
                $this->createOriginAddressRelation($address, $originAddressId);
            }
        }

        $entity->setContact($newContact);
        return $this;
    }

    /**
     * @param ContactAddress $address
     * @param int $originId
     */
    protected function createOriginAddressRelation($address, $originId)
    {
        $addressRelation = new AddressRelation();
        $addressRelation->setOriginId($originId)
            ->setAddress($address);

        $this->getEntityManager(self::ADDRESS_RELATION_ENTITY)
            ->persist($addressRelation);
    }

    /**
     * @param int[] $addressIds
     * @return array
     */
    protected function getOriginAddressesIds($addressIds)
    {
        $qb = $this->getEntityRepository(self::ADDRESS_RELATION_ENTITY)
            ->createQueryBuilder('r');

        $result = $qb->where($qb->expr()->in('r.address', $addressIds))
            ->getQuery()
            ->getResult();

        $items = [];
        foreach ($result as $item) {
            $items[$item->getOriginId()] = $item->getAddress()->getId();
        }

        return $items;
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
