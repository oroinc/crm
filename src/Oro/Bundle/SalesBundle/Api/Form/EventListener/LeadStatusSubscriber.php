<?php

namespace Oro\Bundle\SalesBundle\Api\Form\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use Oro\Bundle\EntityExtendBundle\Provider\EnumValueProvider;

class LeadStatusSubscriber implements EventSubscriberInterface
{
    const STATUS_FIELD_NAME = 'status';

    /** @var EnumValueProvider */
    protected $enumProvider;

    /**
     * @param EnumValueProvider $enumProvider
     */
    public function __construct(EnumValueProvider $enumProvider)
    {
        $this->enumProvider = $enumProvider;
    }

    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::SUBMIT   => 'onSubmit',
        );
    }

    /**
     * @param FormEvent $event
     */
    public function onSubmit(FormEvent $event)
    {
        $lead = $event->getData();

        if (!$lead->getStatus()) {
            $lead->setStatus($this->enumProvider->getDefaultEnumValuesByCode('lead_status')[0]);
        }
        $event->setData($lead);
    }
}
