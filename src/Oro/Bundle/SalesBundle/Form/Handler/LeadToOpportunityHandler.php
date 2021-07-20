<?php

namespace Oro\Bundle\SalesBundle\Form\Handler;

use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\ChannelBundle\Provider\RequestChannelProvider;
use Oro\Bundle\FormBundle\Model\UpdateHandler;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\SalesBundle\Provider\LeadToOpportunityProviderInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class LeadToOpportunityHandler extends OpportunityHandler
{
    /** @var LeadToOpportunityProviderInterface */
    protected $leadToOpportunityProvider;

    /** @var callable */
    protected $errorMessageCallback;

    public function __construct(
        FormInterface $form,
        RequestStack $requestStack,
        ObjectManager $manager,
        RequestChannelProvider $requestChannelProvider,
        LeadToOpportunityProviderInterface $leadToOpportunityProvider,
        LoggerInterface $logger
    ) {
        $this->leadToOpportunityProvider = $leadToOpportunityProvider;
        parent::__construct($form, $requestStack, $manager, $requestChannelProvider, $logger);
    }

    /**
     * @inheritdoc
     */
    public function process(Opportunity $entity)
    {
        $processResult = parent::process($entity);
        if ($processResult && in_array($this->requestStack->getCurrentRequest()->getMethod(), ['POST', 'PUT'], true)) {
            return $this->leadToOpportunityProvider->saveOpportunity($entity, $this->errorMessageCallback);
        }
        return $processResult;
    }

    /**
     * @param Lead          $lead
     * @param UpdateHandler $handler
     * @param string        $saveMessage
     * @param callable      $errorMessageCallback
     *
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function create(
        Lead $lead,
        UpdateHandler $handler,
        $saveMessage,
        callable $errorMessageCallback
    ) {
        $this->errorMessageCallback = $errorMessageCallback;
        $isGetRequest = $this->requestStack->getCurrentRequest()->getMethod() === 'GET';
        $opportunity = $this->leadToOpportunityProvider->prepareOpportunityForForm($lead, $isGetRequest);

        return $handler->update($opportunity, $this->form, $saveMessage, $this);
    }

    protected function onSuccess(Opportunity $entity)
    {
        $this->manager->persist($entity);
    }
}
