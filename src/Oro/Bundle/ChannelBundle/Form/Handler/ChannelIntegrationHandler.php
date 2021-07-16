<?php

namespace Oro\Bundle\ChannelBundle\Form\Handler;

use Oro\Bundle\FormBundle\Utils\FormUtils;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\IntegrationBundle\Form\Handler\ChannelHandler as IntegrationChannelHandler;
use Oro\Bundle\IntegrationBundle\Form\Type\ChannelType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RequestStack;

class ChannelIntegrationHandler
{
    const DATA_PARAM_NAME = 'data';

    /** @var RequestStack */
    protected $requestStack;

    /** @var FormInterface */
    protected $form;

    /** @var array */
    protected $options = ['disable_customer_datasource_types' => false];

    public function __construct(RequestStack $requestStack, FormFactoryInterface $factory)
    {
        $this->requestStack = $requestStack;
        $this->form    = $factory->createNamed(
            'oro_integration_channel_form',
            ChannelType::class,
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

        $request = $this->requestStack->getCurrentRequest();
        $data = $request->get(self::DATA_PARAM_NAME, false);
        if ('POST' === $request->getMethod()) {
            $this->form->handleRequest($request);

            return ($this->form->isSubmitted() && $this->form->isValid());
        } elseif ('GET' === $request->getMethod() && $data) {
            $request->query->set(IntegrationChannelHandler::UPDATE_MARKER, true);
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
        $request = $this->requestStack->getCurrentRequest();
        if ('POST' !== $request->getMethod()) {
            throw new \LogicException('Unable to fetch submitted data, only POST request supported');
        }

        return $request->get($this->form->getName(), []);
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
        $isUpdateOnly = $this->requestStack->getCurrentRequest()->get(IntegrationChannelHandler::UPDATE_MARKER, false);

        $form = $this->form;
        if ($isUpdateOnly) {
            $config = $form->getConfig();
            $form   = $config->getFormFactory()
                ->createNamed(
                    $form->getName(),
                    get_class($config->getType()->getInnerType()),
                    $form->getData(),
                    $this->options
                );
        }

        $view = $form->createView();
        FormUtils::appendClass($view->children['connectors'], 'hide');
        FormUtils::appendClass($view->children['type'], 'hide');

        return $view;
    }
}
