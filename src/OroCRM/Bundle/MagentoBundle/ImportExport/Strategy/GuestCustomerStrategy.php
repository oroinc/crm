<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Strategy;

use OroCRM\Bundle\MagentoBundle\Entity\Customer;

class GuestCustomerStrategy extends AbstractImportStrategy
{
    /**
     * @param Customer $entity
     * @return Customer
     */
    protected function afterProcessEntity($entity)
    {
        $this->processChangeAttributes($entity);

        return parent::afterProcessEntity($entity);
    }

    /**
     * @param Customer $entity
     */
    protected function processChangeAttributes(Customer $entity)
    {
        $itemData = $this->context->getValue('itemData');
        if (empty($itemData['isGuest']) || !$itemData['isGuest']) {
            return;
        }

        $entity->setGuest(true);
        !empty($itemData['customerEmail']) && $entity->setEmail($itemData['customerEmail']);
        if (!empty($itemData['addresses'])) {
            $address = array_shift($itemData['addresses']);
            !empty($address['firstName']) && !$entity->getFirstName() && $entity->setFirstName($address['firstName']);
            !empty($address['lastName']) && !$entity->getLastName() && $entity->setLastName($address['lastName']);
        }
    }
}
