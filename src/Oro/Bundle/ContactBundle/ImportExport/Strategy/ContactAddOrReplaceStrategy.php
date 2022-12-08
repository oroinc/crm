<?php

namespace Oro\Bundle\ContactBundle\ImportExport\Strategy;

use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Entity\ContactAddress;
use Oro\Bundle\ContactBundle\Entity\ContactPhone;
use Oro\Bundle\ImportExportBundle\Strategy\Import\ConfigurableAddOrReplaceStrategy;

/**
 * Import strategy specific for Contact entity.
 */
class ContactAddOrReplaceStrategy extends ConfigurableAddOrReplaceStrategy
{
    protected ContactImportHelper $contactImportHelper;

    public function setContactImportHelper(ContactImportHelper $contactImportHelper): void
    {
        $this->contactImportHelper = $contactImportHelper;
    }

    /**
     * {@inheritdoc}
     */
    protected function beforeProcessEntity($entity): Contact
    {
        /** @var Contact $entity */
        $entity = parent::beforeProcessEntity($entity);

        // need to manually set empty types to skip merge from existing entities
        $itemData = $this->context->getValue('itemData');

        if (!empty($itemData['addresses'])) {
            foreach ($itemData['addresses'] as $key => $address) {
                if (!isset($address['types'])) {
                    $itemData['addresses'][$key]['types'] = array();
                }
            }

            $this->context->setValue('itemData', $itemData);
        }

        return $entity;
    }

    /**
     * {@inheritdoc}
     */
    protected function afterProcessEntity($entity): Contact
    {
        /** @var Contact $entity */
        $entity = parent::afterProcessEntity($entity);

        if ($entity) {
            $this->contactImportHelper->updateScalars($entity);
            $this->contactImportHelper->updatePrimaryEntities($entity);
        }

        return $entity;
    }

    /**
     * Since addresses and phones are always unique for a specific contact and do not have unique identifiers,
     * there is no point in using them again. Also see method: storeNewEntity
     *
     * {@inheritdoc}
     */
    protected function findEntityByIdentityValues($entityName, array $identityValues): ?object
    {
        if (is_a($entityName, ContactPhone::class, true)
            || is_a($entityName, ContactAddress::class, true)
        ) {
            return null;
        }

        // contact last name and first name must be in this order to support compound index
        if (is_a($entityName, Contact::class, true)) {
            if (array_key_exists('firstName', $identityValues) && array_key_exists('lastName', $identityValues)) {
                $firstName = $identityValues['firstName'];
                $lastName = $identityValues['lastName'];
                unset($identityValues['firstName'], $identityValues['lastName']);
                $identityValues = array_merge(
                    array('lastName' => $lastName, 'firstName' => $firstName),
                    $identityValues
                );
            }
        }


        return parent::findEntityByIdentityValues($entityName, $identityValues);
    }

    protected function storeNewEntity(object $entity, array $identityValues = null): ?object
    {
        if (is_a($entity, ContactPhone::class, true) || is_a($entity, ContactAddress::class, true)) {
            return null;
        }

        return parent::storeNewEntity($entity, $identityValues);
    }
}
