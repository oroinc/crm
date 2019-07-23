<?php

namespace Oro\Bundle\SalesBundle\Api\Form\EventListener;

use Oro\Bundle\ApiBundle\Form\FormUtil;
use Oro\Bundle\EntityExtendBundle\Entity\AbstractEnumValue;
use Oro\Bundle\EntityExtendBundle\Provider\EnumValueProvider;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * The form event subscriber responsible for setting default Lead status if Lead doesn't have it,
 * and Lead default status exists.
 */
class LeadStatusSubscriber implements EventSubscriberInterface
{
    /** @var EnumValueProvider */
    private $enumProvider;

    /**
     * @param EnumValueProvider $enumProvider
     */
    public function __construct(EnumValueProvider $enumProvider)
    {
        $this->enumProvider = $enumProvider;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [FormEvents::POST_SUBMIT => 'onPostSubmit'];
    }

    /**
     * @param FormEvent $event
     */
    public function onPostSubmit(FormEvent $event)
    {
        $statusForm = FormUtil::findFormFieldByPropertyPath($event->getForm(), 'status');
        if (null !== $statusForm && $statusForm->isSubmitted()) {
            return;
        }

        /** @var Lead $lead */
        $lead = $event->getData();
        if (null === $lead->getId() && null === $lead->getStatus()) {
            $defaultStatus = $this->getDefaultStatus();
            if (null !== $defaultStatus) {
                $lead->setStatus($defaultStatus);
            }
        }
    }

    /**
     * @return AbstractEnumValue|null
     */
    private function getDefaultStatus(): ?AbstractEnumValue
    {
        $defaultStatuses = $this->enumProvider->getDefaultEnumValuesByCode(Lead::INTERNAL_STATUS_CODE);
        if (empty($defaultStatuses)) {
            return null;
        }

        return \reset($defaultStatuses);
    }
}
