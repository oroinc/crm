<?php

namespace Oro\Bundle\MagentoBundle\Form\EventListener;

use Oro\Bundle\MagentoBundle\Entity\Cart;
use Oro\Bundle\MagentoBundle\Entity\CartItem;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class CartApiFormSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA => 'preSet',
            FormEvents::SUBMIT       => 'submit'
        ];
    }

    /**
     * Modifies form based on data that comes from DB
     *
     * @param FormEvent $event
     */
    public function preSet(FormEvent $event)
    {
        /** @var Cart $data */
        $data = $event->getData();

        if ($data === null) {
            return;
        }

        $data->setCreatedAt(new \DateTime('now'));
        $data->setUpdatedAt(new \DateTime('now'));
    }

    /**
     * @param FormEvent $event
     */
    public function submit(FormEvent $event)
    {
        /** @var Cart $data */
        $data = $event->getData();

        if ($data === null) {
            return;
        }

        $items = $data->getCartItems();

        /** @var CartItem $item */
        foreach ($items as $item) {
            $item->setCart($data);
            $item->setCreatedAt(new \DateTime('now'));
            $item->setUpdatedAt(new \DateTime('now'));
        }
    }
}
