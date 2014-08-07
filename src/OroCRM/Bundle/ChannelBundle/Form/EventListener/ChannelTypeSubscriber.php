<?php

namespace OroCRM\Bundle\ChannelBundle\Form\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

use Oro\Bundle\FormBundle\Utils\FormUtils;

use OroCRM\Bundle\ChannelBundle\Entity\Channel;

class ChannelTypeSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SUBMIT   => 'preSubmit',
        ];
    }

    /**
     * @param FormEvent $event
     */
    public function preSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        if (!empty($data['customerIdentity'])) {
            if (in_array($data['customerIdentity'], $data['entities'])) {
                $value = $data['customerIdentity'];

                $customerIdentityModifier = $this->getCustomerIdentityModifierClosure($value);
                $customerIdentityModifier($form);
            }
        }
    }

    /**
     * @param string $value
     *
     * @return callable
     */
    protected function getCustomerIdentityModifierClosure($value)
    {
        return function (FormInterface $form) use ($value) {
            if (!$value) {
                return;
            }
            FormUtils::replaceField($form, 'customerIdentity', ['data' => $value]);
        };
    }
}
