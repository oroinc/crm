<?php

namespace OroCRM\Bundle\MagentoBundle\ImportExport\Strategy;

use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Entity\Website;

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

        $this->cachedEntities = [];
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
     * @param Customer $entity
     *
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

        $customerStore = $entity->getStore();
        if ($customerStore) {
            $website = $customerStore->getWebsite();

            $entity->setWebsite($website);
            $this->setDefaultGroup($entity, $website);
        }
    }

    /**
     * @param Customer $entity
     * @param Website $customerWebsite
     */
    protected function setDefaultGroup(Customer $entity, Website $customerWebsite)
    {
        if (!$entity->getGroup() && $customerWebsite->getDefaultGroupId()) {
            $em = $this->strategyHelper->getEntityManager('OroCRMMagentoBundle:CustomerGroup');
            $group = $em->getRepository('OroCRMMagentoBundle:CustomerGroup')
                ->findOneBy(
                    [
                        'originId' => $customerWebsite->getDefaultGroupId(),
                        'channel' => $entity->getChannel()
                    ]
                );
            $entity->setGroup($group);
        }
    }
}
