<?php

namespace Oro\Bundle\SalesBundle\Form\Handler;

use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\ChannelBundle\Provider\RequestChannelProvider;
use Oro\Bundle\FormBundle\Model\UpdateHandlerFacade;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\SalesBundle\Provider\LeadToOpportunityProviderInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * The handler for LeadToOpportunity form.
 */
class LeadToOpportunityHandler extends OpportunityHandler
{
    protected LeadToOpportunityProviderInterface $leadToOpportunityProvider;

    /** @var callable */
    protected $errorMessageCallback;

    public function __construct(
        ObjectManager $manager,
        RequestChannelProvider $requestChannelProvider,
        LeadToOpportunityProviderInterface $leadToOpportunityProvider,
        LoggerInterface $logger
    ) {
        $this->leadToOpportunityProvider = $leadToOpportunityProvider;
        parent::__construct($manager, $requestChannelProvider, $logger);
    }

    /**
     * {@inheritDoc}
     */
    public function process($entity, FormInterface $form, Request $request)
    {
        $processResult = parent::process($entity, $form, $request);
        if ($processResult && \in_array($request->getMethod(), ['POST', 'PUT'], true)) {
            return $this->leadToOpportunityProvider->saveOpportunity($entity, $this->errorMessageCallback);
        }

        return $processResult;
    }

    public function create(
        Lead $lead,
        FormInterface $form,
        string $saveMessage,
        Request $request,
        UpdateHandlerFacade $handler,
        callable $errorMessageCallback
    ): array|RedirectResponse {
        $this->errorMessageCallback = $errorMessageCallback;
        $isGetRequest = 'GET' === $request->getMethod();
        $opportunity = $this->leadToOpportunityProvider->prepareOpportunityForForm($lead, $isGetRequest);

        return $handler->update($opportunity, $form, $saveMessage, $request, $this);
    }

    protected function onSuccess(Opportunity $entity): void
    {
        $this->manager->persist($entity);
    }
}
