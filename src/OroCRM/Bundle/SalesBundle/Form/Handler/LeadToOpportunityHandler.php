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
        $lead = $entity->getLead();
        if ($processResult && in_array($this->request->getMethod(), array('POST', 'PUT'))) {
            /** Prepare lead to save */
            $this->setContactAndAccountToLeadFromOpportunity($lead, $entity);
            return $this->changeLeadStatusModel->qualify($lead);
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
        $opportunity = $this->leadToOpportunityProvider->prepareOpportunity($lead, $isGetRequest);
        return $handler->update($opportunity, $this->form, $saveMessage, $this);
    }

    /**
     * @param Lead        $lead
     * @param Opportunity $opportunity
     */
    protected function setContactAndAccountToLeadFromOpportunity(Lead $lead, Opportunity $opportunity)
    {
        $lead->setContact($opportunity->getContact());
        $lead->setCustomer($opportunity->getCustomer());
    }

    /**
     * @param Opportunity $entity
     */
    protected function onSuccess(Opportunity $entity)
    {
        $this->manager->persist($entity);
    }
}
