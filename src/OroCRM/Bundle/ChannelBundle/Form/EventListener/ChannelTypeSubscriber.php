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
            FormEvents::PRE_SET_DATA => 'preSet',
            FormEvents::PRE_SUBMIT   => 'preSubmit',
        ];
    }

    /**
     * @param FormEvent $event
     */
    public function preSet(FormEvent $event)
    {
        $form = $event->getForm();

        /** @var Channel $data */
        $data = $event->getData();

        if ($data === null) {
            return;
        }

        $selectedEntities = $data->getEntities();
        $entityChoices    = $form->get('entities')->getConfig()->getOption('choices');
        $choices          = array_intersect_key($entityChoices, array_flip($selectedEntities));

        $customerIdentityModifier = $this->getCustomerIdentityModifierClosure($choices);
        $customerIdentityModifier($form);
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
                $choices = array_flip([$data['customerIdentity']]);
                $customerIdentityModifier = $this->getCustomerIdentityModifierClosure($choices);
                $customerIdentityModifier($form);
            }
        }
    }

    /**
     * @param array $choices
     *
     * @return callable
     */
    protected function getCustomerIdentityModifierClosure(array $choices)
    {
        return function (FormInterface $form) use ($choices) {
            if (!$choices) {
                return;
            }
            FormUtils::replaceField($form, 'customerIdentity', ['choices' => $choices], ['choice_list']);
        };
    }
}
