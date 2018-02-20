<?php

namespace Oro\Bundle\MagentoBundle\Form\EventListener;

use Oro\Bundle\MagentoBundle\Entity\CartItem;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class CartItemApiFormSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA  => 'preSet'
        ];
    }

    /**
     * Modifies form based on data that comes from DB
     *
     * @param FormEvent $event
     */
    public function preSet(FormEvent $event)
    {
        /** @var CartItem $data */
        $data = $event->getData();

        if ($data === null) {
            return;
        }

        $data->setCreatedAt(new \DateTime('now'));
        $data->setUpdatedAt(new \DateTime('now'));
    }
}
