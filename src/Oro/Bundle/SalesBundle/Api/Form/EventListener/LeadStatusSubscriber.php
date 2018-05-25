<?php

namespace Oro\Bundle\SalesBundle\Api\Form\EventListener;

use Oro\Bundle\SalesBundle\Entity\Lead;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use Oro\Bundle\EntityExtendBundle\Provider\EnumValueProvider;

/**
 * The form event subscriber responsible for setting default Lead status if Lead doesn't have it,
 * and Lead default status exists.
 */
class LeadStatusSubscriber implements EventSubscriberInterface
{
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
        if ($lead->getStatus() !== null) {
            return;
        }

        $leadDefaultStatus = $this->enumProvider->getDefaultEnumValuesByCode(Lead::INTERNAL_STATUS_CODE)[0] ?? null;
        if (!$leadDefaultStatus) {
            return;
        }

        $lead->setStatus($leadDefaultStatus);
        $event->setData($lead);
    }
}
