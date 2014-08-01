<?php

namespace OroCRM\Bundle\ChannelBundle\Form\Handler;

use Oro\Bundle\FormBundle\Utils\FormUtils;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

use Oro\Bundle\IntegrationBundle\Form\Handler\ChannelHandler;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;

class ChannelIntegrationHandler
{
    /** @var Request */
    protected $request;

    /** @var FormInterface */
    protected $form;

    /**
     * @param Request       $request
     * @param FormInterface $form
     */
    public function __construct(Request $request, FormInterface $form)
    {
        $this->request = $request;
        $this->form    = $form;
    }

    /**
     * @param Integration $integration
     *
     * @return bool returns true when form is submitted and valid, false otherwise
     */
    public function process(Integration $integration)
    {
        $this->form->setData($integration);

        if ('POST' === $this->request->getMethod()) {
            $this->form->submit($this->request);

            return (!$this->request->get(ChannelHandler::UPDATE_MARKER, false) && $this->form->isValid());
        }

        return false;
    }

    /**
     * @return string
     */
    public function getFormName()
    {
        return $this->form->getConfig()->getName();
    }

    /**
     * Builds form view in order to show on page, recreates form if update mode
     * due to validation errors should not be shown if submitted in "update mode"
     * NOTE: it was impossible to use validation_groups because of structure of client validation framework
     *
     * @return FormView
     */
    public function getFormView()
    {
        $isUpdateOnly = $this->request->get(ChannelHandler::UPDATE_MARKER, false);

        $form = $this->form;
        if ($isUpdateOnly) {
            $form = $this->form->getConfig()->getFormFactory()
                ->createNamed('oro_integration_channel_form', 'oro_integration_channel_form', $form->getData());
        }

        $view = $form->createView();
        FormUtils::appendClass($view->children['connectors'], 'hide');
        FormUtils::appendClass($view->children['type'], 'hide');

        return $view;
    }
}
