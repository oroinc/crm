<?php

namespace Oro\Bundle\ChannelBundle\EventListener;

use Symfony\Component\HttpFoundation\Request;

use Oro\Bundle\EmbeddedFormBundle\Event\EmbeddedFormSubmitBeforeEvent;
use Oro\Bundle\UIBundle\Event\BeforeFormRenderEvent;
use Oro\Bundle\ChannelBundle\Model\ChannelAwareInterface;

class EmbeddedFormListener
{
    /** @var Request */
    protected $request;

    /**
     * @param Request|null $request
     */
    public function setRequest(Request $request = null)
    {
        $this->request = $request;
    }

    /**
     * Add owner field to forms
     *
     * @param BeforeFormRenderEvent $event
     */
    public function addDataChannelField(BeforeFormRenderEvent $event)
    {
        if (!$this->request) {
            return;
        }

        $routename = $this->request->attributes->get('_route');

        if (strrpos($routename, 'oro_embedded_form_') === 0) {
            $env              = $event->getTwigEnvironment();
            $data             = $event->getFormData();
            $form             = $event->getForm();
            $dataChannelField = $env->render('OroChannelBundle:Form:dataChannelField.html.twig', ['form' => $form]);

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
