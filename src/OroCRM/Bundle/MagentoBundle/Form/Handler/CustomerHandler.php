<?php

namespace OroCRM\Bundle\MagentoBundle\Form\Handler;

use OroCRM\Bundle\MagentoBundle\Entity\Address;
use Symfony\Component\Form\FormInterface;

use Oro\Bundle\FormBundle\Model\UpdateHandler;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Service\StateManager;

class CustomerHandler extends UpdateHandler
{
    /**
     * {@inheritdoc}
     */
    protected function saveForm(FormInterface $form, $entity)
    {
        if (!$entity instanceof Customer) {
            throw new \InvalidArgumentException('Customer expected');
        }

        $form->setData($entity);
        if (in_array($this->request->getMethod(), ['POST', 'PUT'], true)) {
            $form->submit($this->request);

            if ($form->isValid()) {
                if ($entity->getId()) {
                    $this->markForSync($entity);

                    foreach ($entity->getAddresses() as $address) {
                        if ($address->getId()) {
                            $this->markAddressForSync($address);
                            $this->saveEntity($address);
                        }
                    }
                }

                // get address ids to create
                $newAddresses = [];
                foreach ($entity->getAddresses() as $address) {
                    if (!$address->getId()) {
                        $newAddresses[] = $address;
                    }
                }

                $this->saveEntity($entity);

                // Process trigger listen for update, because create will trigger export during import
                // This will schedule new entity for export
                if (!$entity->getOriginId()) {
                    $this->markForSync($entity);
                    $this->saveEntity($entity);
                } else {
                    foreach ($newAddresses as $newAddress) {
                        $this->markAddressForSync($newAddress);
                        $this->saveEntity($newAddress);
                    }
                }

                return true;
            }
        }

        return false;
    }

    /**
     * @param object $entity
     */
    protected function saveEntity($entity)
    {
        $manager = $this->doctrineHelper->getEntityManager($entity);
        $manager->persist($entity);
        $manager->flush($entity);
    }

    /**
     * @param Customer $entity
     */
    protected function markForSync(Customer $entity)
    {
        $stateManager = new StateManager();
        if (!$stateManager->isInState($entity->getSyncState(), Customer::MAGENTO_REMOVED)) {
            $stateManager->addState($entity, 'syncState', Customer::SYNC_TO_MAGENTO);
        }
    }

    /**
     * @param Address $entity
     */
    protected function markAddressForSync(Address $entity)
    {
        $stateManager = new StateManager();
        if (!$stateManager->isInState($entity->getSyncState(), Address::MAGENTO_REMOVED)) {
            $stateManager->addState($entity, 'syncState', Address::SYNC_TO_MAGENTO);
        }
    }
}
