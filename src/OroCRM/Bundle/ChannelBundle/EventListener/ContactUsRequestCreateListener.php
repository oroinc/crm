<?php

namespace OroCRM\Bundle\ChannelBundle\EventListener;

use OroCRM\Bundle\ChannelBundle\Event\ContactUsRequestEvent;
use OroCRM\Bundle\ChannelBundle\Model\ChannelAwareInterface;

class ContactUsRequestCreateListener
{

    /**
     * @param ContactUsRequestEvent $event
     */
    public function onEmbededFormSubmit(ContactUsRequestEvent $event)
    {
        /** @var ChannelAwareInterface $form */
        $form = $event->getFormEntity();
        /** @var  Object */
        $data = $event->getData();

        if ($data instanceof ChannelAwareInterface) {
            $dataChannel = $form->getDataChannel();
            $data->setDataChannel($dataChannel);
        }
    }
}
