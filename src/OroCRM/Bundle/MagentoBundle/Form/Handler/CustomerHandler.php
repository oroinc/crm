<?php

namespace OroCRM\Bundle\MagentoBundle\Form\Handler;

use Symfony\Component\Form\FormInterface;

use Oro\Bundle\FormBundle\Model\UpdateHandler;
use OroCRM\Bundle\MagentoBundle\Entity\Address;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;
use OroCRM\Bundle\MagentoBundle\Service\CustomerStateHandler;

class CustomerHandler extends UpdateHandler
{
    /**
     * @var CustomerStateHandler
     */
    protected $stateHandler;

    /**
     * @param CustomerStateHandler $stateHandler
     * @return CustomerHandler
     */
    public function setStateHandler($stateHandler)
    {
        $this->stateHandler = $stateHandler;

        return $this;
    }

    /**
     * @param Customer $entity
     * @return bool
     */
    public function handleRegister(Customer $entity)
    {
        if ($this->request->getMethod() === 'POST') {
            $manager = $this->doctrineHelper->getEntityManager($entity);
            $entity->setGuest(false);
            $entity->setIsActive(true);
            $this->stateHandler->markCustomerForSync($entity);
            $this->stateHandler->markAddressesForSync($entity);

            $manager->persist($entity);
            $manager->flush();

            return true;
        }

        return false;
    }

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
                $addressesToSync = [];
                if ($entity->getId()) {
                    $this->stateHandler->markCustomerForSync($entity);

                    if (!$entity->getAddresses()->isEmpty()) {
                        foreach ($entity->getAddresses() as $address) {
                            if (!$address->getOriginId()) {
                                $addressesToSync[] = $address;
                            } else {
                                $this->stateHandler->markAddressForSync($address);
                            }
                        }
                    }
                }
                $this->saveEntity($entity);

                // Process trigger listen for update, because create will trigger export during import
                // This will schedule new entity for export
                if (!$entity->getOriginId()) {
                    $this->scheduleCustomerSyncToMagento($entity);
                }

                foreach ($addressesToSync as $address) {
                    $this->scheduleAddressSyncToMagento($address);
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

        // flush entity with related entities
        $manager->flush();
    }

    /**
     * @param Customer $entity
     */
    protected function scheduleCustomerSyncToMagento(Customer $entity)
    {
        $this->stateHandler->markCustomerForSync($entity);
        $this->saveEntity($entity);
    }

    /**
     * @param Address $entity
     */
    protected function scheduleAddressSyncToMagento(Address $entity)
    {
        $this->stateHandler->markAddressForSync($entity);
        $this->saveEntity($entity);
    }
}
