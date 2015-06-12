<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Strategy;

use OroCRM\Bundle\MagentoBundle\Entity\Customer;

class GuestCustomerStrategy extends AbstractImportStrategy
{
    /**
     * {@inheritdoc}
     */
    public function process($entity)
    {
        $this->assertEnvironment($entity);

        if ($this->checkExistingCustomer()) {
            return null;
        }

        $this->cachedEntities = array();
        $entity = $this->beforeProcessEntity($entity);
        $entity = $this->processEntity($entity, true, true, $this->context->getValue('itemData'));
        $entity = $this->afterProcessEntity($entity);
        if ($entity) {
            $entity = $this->validateAndUpdateContext($entity);
        }

        return $entity;
    }

    /**
     * @return Customer
     */
    protected function checkExistingCustomer()
    {
        $itemData = $this->context->getValue('itemData');
        if (!array_key_exists('customerEmail', $itemData)) {
            return null;
        }

        $email = $itemData['customerEmail'];
        $existingCustomer = $this->databaseHelper->findOneBy(
            'OroCRM\Bundle\MagentoBundle\Entity\Customer',
            ['email' => $email]
        );

        return $existingCustomer;
    }

    /**
     * {@inheritdoc}
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
        $entity->setGuest(true);
        $entity->setConfirmed(false);
        foreach ($entity->getAddresses() as $address) {
            $address->setOriginId(null);
        }

        $em = $this->databaseHelper->getRegistry()->getManager();
        if (!empty($itemData['customer_group_id']) && !$entity->getGroup()) {
            $group = $em->getRepository('OroCRMMagentoBundle:CustomerGroup')
                ->findOneBy(['originId' => $itemData['customer_group_id']]);
            $entity->setGroup($group);
        }
        if (!empty($itemData['store']['originId'])) {
            $store = $em->getRepository('OroCRMMagentoBundle:Store')
                ->findOneBy(['originId' => $itemData['store']['originId']]);
            $entity->setWebsite($store->getWebsite());
            $entity->setCreatedIn($store->getName());
        }

        !empty($itemData['customerEmail']) && $entity->setEmail($itemData['customerEmail']);
        if (!empty($itemData['addresses'])) {
            $address = array_pop($itemData['addresses']);
            !empty($address['firstName']) && !$entity->getFirstName() && $entity->setFirstName($address['firstName']);
            !empty($address['lastName']) && !$entity->getLastName() && $entity->setLastName($address['lastName']);
        }
    }
}
