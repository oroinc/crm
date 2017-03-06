<?php

namespace Oro\Bundle\SalesBundle\Form\Handler;

use Psr\Log\LoggerInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\FormBundle\Model\UpdateHandler;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Oro\Bundle\ChannelBundle\Provider\RequestChannelProvider;
use Oro\Bundle\SalesBundle\Provider\LeadToOpportunityProviderInterface;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class LeadToOpportunityHandler extends OpportunityHandler
{
    /** @var LeadToOpportunityProviderInterface */
    protected $leadToOpportunityProvider;

    /** @var callable */
    protected $errorMessageCallback;

    public function __construct(
        FormInterface $form,
        Request $request,
        ObjectManager $manager,
        RequestChannelProvider $requestChannelProvider,
        LeadToOpportunityProviderInterface $leadToOpportunityProvider,
        LoggerInterface $logger
    ) {
        $this->leadToOpportunityProvider = $leadToOpportunityProvider;
        parent::__construct($form, $request, $manager, $requestChannelProvider, $logger);
    }

    /**
     * @inheritdoc
     */
    public function process(Opportunity $entity)
    {
        $processResult = parent::process($entity);
        if ($processResult && in_array($this->request->getMethod(), array('POST', 'PUT'))) {
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
        $isGetRequest = $this->request->getMethod() === 'GET';
        $opportunity = $this->leadToOpportunityProvider->prepareOpportunityForForm($lead, $isGetRequest);
        return $handler->update($opportunity, $this->form, $saveMessage, $this);
    }

    /**
     * @param Opportunity $entity
     */
    protected function onSuccess(Opportunity $entity)
    {
        $this->manager->persist($entity);
    }
}
