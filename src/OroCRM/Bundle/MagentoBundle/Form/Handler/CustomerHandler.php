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
                $this->saveEntity($entity);

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
    protected function saveEntity($entity)
    {
        $manager = $this->doctrineHelper->getEntityManager($entity);
        $manager->persist($entity);
        $manager->flush();
    }

    /**
     * @param Customer $entity
     */
    protected function scheduleSyncToMagento($entity)
    {
        $stateManager = new StateManager();
        if (!$stateManager->isInState($entity->getSyncState(), Customer::MAGENTO_REMOVED)) {
            $stateManager->addState($entity, 'syncState', Customer::SYNC_TO_MAGENTO);
            $this->saveEntity($entity);
        }
    }
}
