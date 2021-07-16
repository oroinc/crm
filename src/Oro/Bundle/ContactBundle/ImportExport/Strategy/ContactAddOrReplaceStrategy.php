<?php

namespace Oro\Bundle\ContactBundle\ImportExport\Strategy;

use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ImportExportBundle\Strategy\Import\ConfigurableAddOrReplaceStrategy;

/**
 * Import strategy specific for Contact entity.
 */
class ContactAddOrReplaceStrategy extends ConfigurableAddOrReplaceStrategy
{
    /**
     * @var ContactImportHelper
     */
    protected $contactImportHelper;

    public function setContactImportHelper(ContactImportHelper $contactImportHelper)
    {
        $this->contactImportHelper = $contactImportHelper;
    }

    /**
     * {@inheritdoc}
     */
    protected function beforeProcessEntity($entity)
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
    protected function afterProcessEntity($entity)
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
     * {@inheritdoc}
     */
    protected function findEntityByIdentityValues($entityName, array $identityValues)
    {
        // contact last name and first name must be in this order to support compound index
        if (is_a($entityName, Contact::class, true)) {
            if (array_key_exists('firstName', $identityValues) && array_key_exists('lastName', $identityValues)) {
                $firstName = $identityValues['firstName'];
                $lastName = $identityValues['lastName'];
                unset($identityValues['firstName']);
                unset($identityValues['lastName']);
                $identityValues = array_merge(
                    array('lastName' => $lastName, 'firstName' => $firstName),
                    $identityValues
                );
            }
        }

        return parent::findEntityByIdentityValues($entityName, $identityValues);
    }
}
