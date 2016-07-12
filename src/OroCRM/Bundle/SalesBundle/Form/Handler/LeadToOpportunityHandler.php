<?php

namespace OroCRM\Bundle\SalesBundle\Form\Handler;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\FormBundle\Model\UpdateHandler;

use OroCRM\Bundle\SalesBundle\Entity\Opportunity;
use OroCRM\Bundle\SalesBundle\Entity\Lead;
use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\ChannelBundle\Provider\RequestChannelProvider;
use OroCRM\Bundle\SalesBundle\Model\ChangeLeadStatus;
use OroCRM\Bundle\SalesBundle\Provider\LeadToOpportunityProvider;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class LeadToOpportunityHandler extends OpportunityHandler
{
    /** @var RequestChannelProvider */
    protected $changeLeadStatusModel;

    /** @var LeadToOpportunityProvider */
    protected $leadToOpportunityProvider;

    public function __construct(
        FormInterface $form,
        Request $request,
        ObjectManager $manager,
        RequestChannelProvider $requestChannelProvider,
        ChangeLeadStatus $changeLeadStatusModel,
        LeadToOpportunityProvider $leadToOpportunityProvider
    ) {
        $this->leadToOpportunityProvider = $leadToOpportunityProvider;
        $this->changeLeadStatusModel = $changeLeadStatusModel;
        parent::__construct($form, $request, $manager, $requestChannelProvider);
    }

    /**
     * @inheritdoc
     */
    public function process(Opportunity $entity)
    {
        $processResult = parent::process($entity);
        if ($processResult && in_array($this->request->getMethod(), array('POST', 'PUT'))) {
            $this->leadToOpportunityProvider->prepareOpportunityToSave($entity);
            return $this->changeLeadStatusModel->qualify($entity->getLead());
        }
        return $processResult;
    }

    /**
     * @param Lead          $lead
     * @param UpdateHandler $handler
     * @param string        $saveMessage
     *
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function create(Lead $lead, UpdateHandler $handler, $saveMessage)
    {
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
