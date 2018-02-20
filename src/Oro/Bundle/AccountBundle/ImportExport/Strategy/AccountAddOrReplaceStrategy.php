<?php

namespace Oro\Bundle\AccountBundle\ImportExport\Strategy;

use Doctrine\Common\Util\ClassUtils;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\ImportExportBundle\Strategy\Import\ConfigurableAddOrReplaceStrategy;

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
        $fieldName = 'contacts';

        if ($entity instanceof Account
            && $existingEntity instanceof Account
            && !$this->isFieldExcluded($entityName, $fieldName, $itemData)
            && !in_array($fieldName, $excludedFields)
        ) {
            foreach ($existingEntity->getContacts() as $contact) {
                $existingEntity->removeContact($contact);
            }

            foreach ($entity->getContacts() as $contact) {
                $contact->removeAccount($entity);
                $existingEntity->addContact($contact);
            }

            $excludedFields[] = $fieldName;
        }

        parent::importExistingEntity($entity, $existingEntity, $itemData, $excludedFields);
    }
}
