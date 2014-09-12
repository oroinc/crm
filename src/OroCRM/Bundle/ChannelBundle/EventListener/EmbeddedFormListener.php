<?php

namespace OroCRM\Bundle\ChannelBundle\EventListener;

use Oro\Bundle\EmbeddedFormBundle\Event\EmbeddedFormSubmitBeforeEvent;
use Oro\Bundle\UIBundle\Event\BeforeFormRenderEvent;

use OroCRM\Bundle\ChannelBundle\Model\ChannelAwareInterface;

class EmbeddedFormListener
{
    /**
     * Add owner field to forms
     *
     * @param BeforeFormRenderEvent $event
     */
    public function addDataChannelField(BeforeFormRenderEvent $event)
    {
        $env              = $event->getTwigEnvironment();
        $data             = $event->getFormData();
        $form             = $event->getForm();
        $dataChannelField = $env->render('OroCRMChannelBundle:Form:dataChannelField.html.twig', ['form' => $form]);

        /**
         * Setting dataChannel field as first field in first data block
         */
        if (!empty($data['dataBlocks'])) {
            if (isset($data['dataBlocks'][0]['subblocks'])) {
                array_unshift($data['dataBlocks'][0]['subblocks'][0]['data'], $dataChannelField);
            }
        }

        $event->setFormData($data);
    }

    /**
     * @param EmbeddedFormSubmitBeforeEvent $event
     */
    public function onEmbeddedFormSubmit(EmbeddedFormSubmitBeforeEvent $event)
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
