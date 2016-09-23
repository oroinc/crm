<?php

namespace Oro\Bundle\ChannelBundle\Form\Handler;

use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormFactoryInterface;

use Oro\Bundle\FormBundle\Utils\FormUtils;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\IntegrationBundle\Form\Handler\ChannelHandler as IntegrationChannelHandler;

class ChannelIntegrationHandler
{
    const DATA_PARAM_NAME = 'data';

    /** @var Request */
    protected $request;

    /** @var FormInterface */
    protected $form;

    /** @var array */
    protected $options = ['disable_customer_datasource_types' => false];

    /**
     * @param Request              $request
     * @param FormFactoryInterface $factory
     */
    public function __construct(Request $request, FormFactoryInterface $factory)
    {
        $this->request = $request;
        $this->form    = $factory->createNamed(
            'oro_integration_channel_form',
            'oro_integration_channel_form',
            null,
            $this->options
        );
    }

    /**
     * @param Integration $integration
     *
     * @return bool returns true when form is submitted and valid, false otherwise
     */
    public function process(Integration $integration)
    {
        $this->form->setData($integration);

        $data = $this->request->get(self::DATA_PARAM_NAME, false);
        if ('POST' === $this->request->getMethod()) {
            $this->form->submit($this->request);

            return ($this->form->isValid());
        } elseif ('GET' === $this->request->getMethod() && $data) {
            $this->request->query->set(IntegrationChannelHandler::UPDATE_MARKER, true);
            $this->form->submit($data);
        }

        return false;
    }

    /**
     * @throws \LogicException
     *
     * @return array
     */
    public function getFormSubmittedData()
    {
        if ('POST' !== $this->request->getMethod()) {
            throw new \LogicException('Unable to fetch submitted data, only POST request supported');
        }

        return $this->request->get($this->form->getName(), []);
    }

    /**
     * Builds form view in order to show on page, recreates form if update mode
     * due to validation errors should not be shown if submitted in 'update mode'
     * NOTE: it was impossible to use validation_groups because of structure of client validation framework
     *
     * @return FormView
     */
    public function getFormView()
    {
        $isUpdateOnly = $this->request->get(IntegrationChannelHandler::UPDATE_MARKER, false);

        $form = $this->form;
        if ($isUpdateOnly) {
            $config = $form->getConfig();
            $form   = $config->getFormFactory()
                ->createNamed($form->getName(), $config->getType()->getName(), $form->getData(), $this->options);
        }

        $view = $form->createView();
        FormUtils::appendClass($view->children['connectors'], 'hide');
        FormUtils::appendClass($view->children['type'], 'hide');

        return $view;
    }
}
