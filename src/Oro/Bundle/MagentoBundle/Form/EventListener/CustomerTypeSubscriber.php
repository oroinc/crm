<?php

namespace Oro\Bundle\MagentoBundle\Form\EventListener;

use Oro\Bundle\MagentoBundle\Entity\Customer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class CustomerTypeSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SUBMIT  => 'preSubmit',
            FormEvents::SUBMIT  => 'onSubmit'
        ];
    }

    /**
     * @param FormEvent $formEvent
     */
    public function preSubmit(FormEvent $formEvent)
    {
        /** @var Customer $entity */
        $entity = $formEvent->getForm()->getData();

        $date = new \DateTime('now', new \DateTimeZone('UTC'));
        if (!$entity->getCreatedAt()) {
            $entity->setCreatedAt($date);
        }

        $entity->setUpdatedAt($date);
    }

    /**
     * @param FormEvent $formEvent
     */
    public function onSubmit(FormEvent $formEvent)
    {
        /** @var Customer $entity */
        $entity = $formEvent->getForm()->getData();

        $dataChannel = $entity->getDataChannel();
        if ($dataChannel) {
            $entity->setChannel($dataChannel->getDataSource());
        }
    }
}
