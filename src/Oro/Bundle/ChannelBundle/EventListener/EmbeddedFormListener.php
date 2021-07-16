<?php

namespace Oro\Bundle\ChannelBundle\EventListener;

use Oro\Bundle\ChannelBundle\Model\ChannelAwareInterface;
use Oro\Bundle\EmbeddedFormBundle\Event\EmbeddedFormSubmitBeforeEvent;
use Oro\Bundle\UIBundle\Event\BeforeFormRenderEvent;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Add dataChannel field and pre-set dataChannel to entities that implements ChannelAwareInterface.
 */
class EmbeddedFormListener
{
    /** @var RequestStack */
    protected $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * Add owner field to forms
     */
    public function addDataChannelField(BeforeFormRenderEvent $event)
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return;
        }

        $routename = $request->attributes->get('_route');

        if (strrpos($routename, 'oro_embedded_form_') === 0) {
            $env              = $event->getTwigEnvironment();
            $data             = $event->getFormData();
            $form             = $event->getForm();
            $dataChannelField = $env->render('@OroChannel/Form/dataChannelField.html.twig', ['form' => $form]);

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
