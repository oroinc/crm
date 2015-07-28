<?php

namespace OroCRM\Bundle\MagentoBundle\Form\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use OroCRM\Bundle\MagentoBundle\Entity\Cart;
use OroCRM\Bundle\MagentoBundle\Entity\CartItem;

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
