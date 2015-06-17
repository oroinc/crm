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

        if ($this->checkExistingCustomer($entity)) {
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
     * @param Customer $entity
     *
     * @return Customer
     */
    protected function checkExistingCustomer(Customer $entity)
    {
        if (!$entity->getEmail()) {
            return null;
        }

        $existingCustomer = $this->databaseHelper->findOneBy(
            'OroCRM\Bundle\MagentoBundle\Entity\Customer',
            [
                'email' => $entity->getEmail(),
                'channel' => $entity->getChannel()
            ]
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
        foreach ($entity->getAddresses() as $address) {
            $address->setOriginId(null);
        }

        $entity->setWebsite($entity->getStore()->getWebsite());
        $entity->setCreatedIn($entity->getStore()->getName());
        $this->setDefaultGroup($entity);
    }

    /**
     * @param Customer $entity
     */
    protected function setDefaultGroup(Customer $entity)
    {
        $em = $this->databaseHelper->getRegistry()->getManager();
        if (!$entity->getGroup() && $entity->getWebsite()->getDefaultGroupId()) {
            $group = $em->getRepository('OroCRMMagentoBundle:CustomerGroup')
                ->findOneBy(
                    [
                        'originId' => $entity->getWebsite()->getDefaultGroupId(),
                        'channel' => $entity->getChannel()
                    ]
                );
            $entity->setGroup($group);
        }
    }
}
