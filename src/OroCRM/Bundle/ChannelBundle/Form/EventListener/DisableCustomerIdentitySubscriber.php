<?php

namespace OroCRM\Bundle\ChannelBundle\Form\EventListener;

use Oro\Bundle\FormBundle\Utils\FormUtils;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class DisableCustomerIdentitySubscriber implements EventSubscriberInterface
{
    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
     *
     * @return array The event names to listen to
     *
     * @api
     */
    public static function getSubscribedEvents()
    {
        return array(FormEvents::PRE_SET_DATA => 'preSetData');
    }

    public function preSetData(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        if (null === $data) {
            return;
        }

        if (($data && $data->getId())) {
            $field  = $form->get('customerIdentity');
            $config = $field->getConfig()->getOptions();

            FormUtils::replaceField(
                $form,
                'customerIdentity',
                ['required' => false, 'disabled' => true]
            );
            FormUtils::replaceField(
                $form,
                'channelType',
                ['required' => false, 'disabled' => true]
            );
        }
    }
}
