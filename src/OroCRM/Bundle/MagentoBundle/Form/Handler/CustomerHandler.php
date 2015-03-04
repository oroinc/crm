<?php

namespace OroCRM\Bundle\MagentoBundle\Form\Handler;

use Symfony\Component\Form\FormInterface;

use Oro\Bundle\FormBundle\Model\UpdateHandler;
use OroCRM\Bundle\MagentoBundle\Entity\Customer;

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
                $dataChannel = $entity->getDataChannel();
                if ($dataChannel) {
                    $entity->setChannel($dataChannel->getDataSource());
                }

                $store = $entity->getStore();
                if ($store) {
                    $entity->setWebsite($store->getWebsite());
                }

                $manager = $this->doctrineHelper->getEntityManager($entity);
                $manager->persist($entity);
                $manager->flush();

                return true;
            }
        }

        return false;
    }
}
