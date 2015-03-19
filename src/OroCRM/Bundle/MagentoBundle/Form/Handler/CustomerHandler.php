<?php

namespace OroCRM\Bundle\MagentoBundle\Form\Handler;

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
                $this->markForSync($entity);
                $this->saveEntity($entity);

                // Process trigger listen for update, because create will trigger export during import
                // This will schedule new entity for export
                if (!$entity->getOriginId()) {
                    $this->scheduleSyncToMagento($entity);
                }

                return true;
            }
        }

        return false;
    }

    /**
     * @param Customer $entity
     */
    protected function saveEntity(Customer $entity)
    {
        $manager = $this->doctrineHelper->getEntityManager($entity);
        $manager->persist($entity);
        $manager->flush($entity);
    }

    /**
     * @param Customer $entity
     */
    protected function scheduleSyncToMagento($entity)
    {
        $this->markForSync($entity);
        $this->saveEntity($entity);
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
}
