<?php

namespace OroCRM\Bundle\SalesBundle\Form\Handler;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\FormBundle\Model\UpdateHandler;

use OroCRM\Bundle\SalesBundle\Entity\Opportunity;
use OroCRM\Bundle\SalesBundle\Entity\Lead;
use OroCRM\Bundle\ChannelBundle\Provider\RequestChannelProvider;
use OroCRM\Bundle\SalesBundle\Provider\LeadToOpportunityProvider;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class LeadToOpportunityHandler extends OpportunityHandler
{
    /** @var LeadToOpportunityProvider */
    protected $leadToOpportunityProvider;

    /** @var callable */
    protected $errorMessageCallback;

    public function __construct(
        FormInterface $form,
        Request $request,
        ObjectManager $manager,
        RequestChannelProvider $requestChannelProvider,
        LeadToOpportunityProvider $leadToOpportunityProvider
    ) {
        $this->leadToOpportunityProvider = $leadToOpportunityProvider;
        parent::__construct($form, $request, $manager, $requestChannelProvider);
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
     * @param callable      $saveRouteCallback
     * @param callable      $errorMessageCallback
     *
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function create(
        Lead $lead,
        UpdateHandler $handler,
        $saveMessage,
        callable $saveRouteCallback,
        callable $errorMessageCallback
    ) {
        $this->errorMessageCallback = $errorMessageCallback;
        $isGetRequest = $this->request->getMethod() === 'GET';
        $opportunity = $this->leadToOpportunityProvider->prepareOpportunityForForm($lead, $isGetRequest);
        return $handler->handleUpdate(
            $opportunity,
            $this->form,
            $saveRouteCallback,
            $saveRouteCallback,
            $saveMessage,
            $this
        );
    }

    /**
     * @param Opportunity $entity
     */
    protected function onSuccess(Opportunity $entity)
    {
        $this->manager->persist($entity);
    }
}
