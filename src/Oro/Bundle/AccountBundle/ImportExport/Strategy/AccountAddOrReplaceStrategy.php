<?php

namespace Oro\Bundle\AccountBundle\ImportExport\Strategy;

use Doctrine\Common\Util\ClassUtils;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ImportExportBundle\Strategy\Import\ConfigurableAddOrReplaceStrategy;

/**
 * Handles logic of changing values in fields "contacts" and "defaultContact" in case updating an existing entity
 */
class AccountAddOrReplaceStrategy extends ConfigurableAddOrReplaceStrategy
{
    /**
     * {@inheritdoc}
     */
    protected function importExistingEntity(
        $entity,
        $existingEntity,
        $itemData = null,
        array $excludedFields = array()
    ) {
        // manually handle recursive relation to contacts
        $entityName = ClassUtils::getClass($entity);

        if ($entity instanceof Account && $existingEntity instanceof Account) {
            if (!$this->isFieldExcluded($entityName, 'defaultContact', $itemData)) {
                $this->processChangesInFieldDefaultContact($entity, $existingEntity);

                $excludedFields[] = 'defaultContact';
            }

            if (!$this->isFieldExcluded($entityName, 'contacts', $itemData) &&
                !in_array('contacts', $excludedFields, true)) {
                $this->processChangesInFieldContacts($entity, $existingEntity);

                $excludedFields[] = 'contacts';
            }
        }

        parent::importExistingEntity($entity, $existingEntity, $itemData, $excludedFields);
    }

    private function processChangesInFieldDefaultContact(Account $entity, Account $existingEntity): void
    {
        $defaultContact = $entity->getDefaultContact();
        $existingDefaultContact = $existingEntity->getDefaultContact();
        if ($existingDefaultContact instanceof Contact &&
            $entity->getDefaultContact() !== $existingEntity->getDefaultContact()) {
            $existingDefaultContact->removeDefaultInAccount($existingEntity);
            $existingDefaultContact->removeAccount($existingEntity);
        }

        if ($defaultContact instanceof Contact) {
            $defaultContact->removeDefaultInAccount($entity);
            $defaultContact->removeAccount($entity);
            $existingEntity->setDefaultContact($defaultContact);
        }
    }

    private function processChangesInFieldContacts(Account $entity, Account $existingEntity): void
    {
        foreach ($existingEntity->getContacts() as $contact) {
            if ($existingEntity->getDefaultContact() === $contact) {
                continue;
            }
            $existingEntity->removeContact($contact);
        }

        foreach ($entity->getContacts() as $contact) {
            $contact->removeAccount($entity);
            $existingEntity->addContact($contact);
        }
    }
}
