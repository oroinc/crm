<?php

namespace Oro\Bundle\FlexibleEntityBundle\Form\EventListener;

use Oro\Bundle\FlexibleEntityBundle\Entity\Collection;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CollectionTypeSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::POST_BIND  => 'postBind',
//            FormEvents::PRE_SET_DATA  => 'preSet',
        );
    }

//    public function preSet(FormEvent $event)
//    {
//        $data = $event->getData();
//        $collection = $data->getCollections();
//
//        if ($collection->isEmpty()) {
//            $collection->add(new Collection());
//        }
//    }

    /**
     * Removes empty collection elements
     *
     * @param FormEvent $event
     */
    public function postBind(FormEvent $event)
    {
        $data = $event->getData();

        $collection = $data->getCollections();
        foreach ($collection as $k => $item) {
            if (is_null($item) || $item->__toString() == '') {
                $collection->remove($k);
            }
        }
    }
}
