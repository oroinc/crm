<?php

namespace Oro\Bundle\ContactBundle\ImportExport\Strategy;

use Oro\Bundle\AddressBundle\Entity\Country;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Entity\ContactAddress;
use Oro\Bundle\ImportExportBundle\Strategy\Import\AbstractImportStrategy;
use Oro\Bundle\SecurityBundle\Authentication\Token\OrganizationAwareTokenInterface;
use Oro\Bundle\UserBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * The import strategy for adding new Contact entities.
 */
class ContactAddStrategy extends AbstractImportStrategy
{
    /**
     * @var ContactImportHelper
     */
    protected $contactImportHelper;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    public function setContactImportHelper(ContactImportHelper $contactImportHelper)
    {
        $this->contactImportHelper = $contactImportHelper;
    }

    public function setTokenStorage(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function process($entity)
    {
        $this->assertEnvironment($entity);

        /** @var Contact $entity */
        $entity = $this->beforeProcessEntity($entity);
        $entity = $this->processEntity($entity);
        $entity = $this->afterProcessEntity($entity);
        $entity = $this->validateAndUpdateContext($entity);

        return $entity;
    }

    /**
     * @param Contact $entity
     * @return Contact
     */
    protected function processEntity(Contact $entity)
    {
        $this->databaseHelper->resetIdentifier($entity);

        $this->processSingleRelations($entity);
        $this->processMultipleRelations($entity);
        $this->processSecurityRelations($entity);

        return $entity;
    }

    protected function processSingleRelations(Contact $entity)
    {
        // update source
        $source = $entity->getSource();
        if ($source) {
            $entity->setSource($this->findExistingEntity($source));
        }

        // update method
        $method = $entity->getMethod();
        if ($method) {
            $entity->setMethod($this->findExistingEntity($method));
        }

        // update assigned to
        $assignedTo = $entity->getAssignedTo();
        if ($assignedTo) {
            $entity->setAssignedTo($this->findExistingEntity($assignedTo));
        }

        // clear reports to
        $entity->setReportsTo(null);

        // update created by
        $createdBy = $entity->getCreatedBy();
        if ($createdBy) {
            $entity->setCreatedBy($this->findExistingEntity($createdBy));
        }

        // update updated by
        $updatedBy = $entity->getUpdatedBy();
        if ($updatedBy) {
            $entity->setUpdatedBy($this->findExistingEntity($updatedBy));
        }
    }

    protected function processMultipleRelations(Contact $entity)
    {
        // update groups
        foreach ($entity->getGroups() as $group) {
            $entity->removeGroup($group);
            if ($group = $this->findExistingEntity($group)) {
                $entity->addGroup($group);
            }
        }

        // update accounts
        foreach ($entity->getAccounts() as $account) {
            $entity->removeAccount($account);
            if ($account = $this->findExistingEntity($account)) {
                $entity->addAccount($account);
            }
        }

        // update addresses
        /** @var ContactAddress $contactAddress */
        foreach ($entity->getAddresses() as $contactAddress) {
            $existingCountry = $this->findExistingEntity($contactAddress->getCountry());
            if ($existingCountry instanceof Country) {
                $contactAddress->setCountry($existingCountry);
            }

            // update address types
            foreach ($contactAddress->getTypes() as $addressType) {
                $contactAddress->removeType($addressType);
                $existingAddressType = $this->findExistingEntity($addressType);
                if ($existingAddressType) {
                    $contactAddress->addType($existingAddressType);
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function afterProcessEntity($entity)
    {
        /** @var Contact $entity */
        $entity = parent::afterProcessEntity($entity);

        $this->contactImportHelper->updateScalars($entity);
        $this->contactImportHelper->updatePrimaryEntities($entity);

        return $entity;
    }

    protected function processSecurityRelations(Contact $entity)
    {
        // update owner
        $owner = $entity->getOwner();
        if ($owner) {
            $owner = $this->findExistingEntity($owner);
        }
        if (!$owner) {
            $token = $this->tokenStorage->getToken();
            if ($token && ($user = $token->getUser()) instanceof User) {
                $owner = $user;
            }
        }
        $entity->setOwner($owner);

        // update organization
        $organization = $entity->getOrganization();
        if ($organization) {
            $organization = $this->findExistingEntity($organization);
        }
        if (!$organization) {
            $token = $this->tokenStorage->getToken();
            if ($token && $token instanceof OrganizationAwareTokenInterface) {
                $organization = $token->getOrganization();
            }
        }
        $entity->setOrganization($organization);
    }

    /**
     * @param Contact $entity
     * @return null|Contact
     */
    protected function validateAndUpdateContext(Contact $entity)
    {
        // validate entity
        $validationErrors = $this->strategyHelper->validateEntity($entity);
        if ($validationErrors) {
            $this->context->incrementErrorEntriesCount();
            $this->strategyHelper->addValidationErrors($validationErrors, $this->context);
            return null;
        }

        // increment context counter
        $this->context->incrementAddCount();

        return $entity;
    }
}
