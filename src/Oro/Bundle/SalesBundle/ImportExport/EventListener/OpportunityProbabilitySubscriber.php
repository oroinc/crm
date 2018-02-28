<?php

namespace Oro\Bundle\SalesBundle\ImportExport\EventListener;

use Oro\Bundle\FormBundle\Form\DataTransformer\PercentToLocalizedStringTransformer;
use Oro\Bundle\ImportExportBundle\Event\DenormalizeEntityEvent;
use Oro\Bundle\ImportExportBundle\Event\Events;
use Oro\Bundle\ImportExportBundle\Event\NormalizeEntityEvent;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OpportunityProbabilitySubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::BEFORE_NORMALIZE_ENTITY => 'beforeNormalize',
            Events::AFTER_DENORMALIZE_ENTITY => 'afterDenormalize',
        ];
    }

    /**
     * @param NormalizeEntityEvent $event
     * @return array
     */
    public function beforeNormalize(NormalizeEntityEvent $event)
    {
        $object = $event->getObject();

        if ($object instanceof Opportunity && $object->getProbability()) {
            $transformer = new PercentToLocalizedStringTransformer();
            $object->setProbability($transformer->transform($object->getProbability()));
        }

        return $event->getResult();
    }

    /**
     * @param DenormalizeEntityEvent $event
     * @return object
     */
    public function afterDenormalize(DenormalizeEntityEvent $event)
    {
        $object = $event->getObject();

        if ($object instanceof Opportunity && $object->getProbability()) {
            $transformer = new PercentToLocalizedStringTransformer();
            $object->setProbability($transformer->reverseTransform($object->getProbability()));
        }

        return $object;
    }
}
