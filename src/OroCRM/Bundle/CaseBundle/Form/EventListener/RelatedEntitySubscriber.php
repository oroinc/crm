<?php

namespace OroCRM\Bundle\CaseBundle\Form\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

class RelatedEntitySubscriber implements EventSubscriberInterface
{
    /**
     * @var string
     */
    protected $entityChoiceName;

    /**
     * @var array
     */
    protected $relatedEntityProperties;

    /**
     * @var PropertyAccessor
     */
    static protected $propertyAccessor;

    /**
     * @param string $entityChoiceName
     * @param array $relatedEntityProperties
     */
    public function __construct($entityChoiceName, array $relatedEntityProperties)
    {
        $this->entityChoiceName = $entityChoiceName;
        $this->relatedEntityProperties = $relatedEntityProperties;
    }

    /**
     * @param FormEvent $event
     */
    public function postSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        if (!$form->has($this->entityChoiceName)) {
            return;
        }

        $entity = $event->getData();

        $selectedEntity = $form->get($this->entityChoiceName)->getData();
        $accessor = $this->getPropertyAccessor();
        foreach ($this->relatedEntityProperties as $relatedEntityProperty) {
            if ($selectedEntity !== $relatedEntityProperty) {
                $accessor->setValue($entity, $relatedEntityProperty, null);
            }
        }
    }

    /**
     * @param FormEvent $event
     */
    public function postSetData(FormEvent $event)
    {
        $form = $event->getForm();
        if (!$form->has($this->entityChoiceName)) {
            return;
        }

        $entity = $event->getData();

        $selectedEntity = null;
        $accessor = $this->getPropertyAccessor();
        foreach ($this->relatedEntityProperties as $relatedEntityProperty) {
            if (!$selectedEntity && $accessor->getValue($entity, $relatedEntityProperty)) {
                $selectedEntity = $relatedEntityProperty;
                $form->get($this->entityChoiceName)->setData($selectedEntity);
            }
        }
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::POST_SUBMIT => 'postSubmit',
            FormEvents::POST_SET_DATA => 'postSetData',
        );
    }

    /**
     * @return PropertyAccessor
     */
    protected function getPropertyAccessor()
    {
        if (!self::$propertyAccessor) {
            self::$propertyAccessor = PropertyAccess::createPropertyAccessor();
        }

        return self::$propertyAccessor;
    }
}
