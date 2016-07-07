<?php

namespace OroCRM\Bundle\SalesBundle\Form\Handler;

use Doctrine\Common\Persistence\ObjectManager;

use OroCRM\Bundle\SalesBundle\Entity\Opportunity;
use OroCRM\Bundle\SalesBundle\Entity\Lead;
use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\ChannelBundle\Provider\RequestChannelProvider;
use OroCRM\Bundle\SalesBundle\Model\ChangeLeadStatus;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class LeadToOpportunityHandler extends OpportunityHandler
{
    /** @var RequestChannelProvider */
    protected $changeLeadStatusModel;

    public function __construct(
        FormInterface $form,
        Request $request,
        ObjectManager $manager,
        RequestChannelProvider $requestChannelProvider,
        ChangeLeadStatus $changeLeadStatusModel
    ) {
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
