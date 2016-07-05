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
        if ($processResult && in_array($this->request->getMethod(), array('POST', 'PUT'))) {
            $this->attachContactToLead($entity->getLead(), $entity);
            return $this->changeLeadStatusModel->qualify($entity->getLead());
        }
        return $processResult;
    }

    /**
     * @param Lead        $lead
     * @param Opportunity $opportunity
     */
    protected function attachContactToLead(Lead $lead, Opportunity $opportunity)
    {
        if (!$lead->getContact() instanceof Contact) {
            $lead->setContact($opportunity->getContact());
        }
    }

    /**
     * @param Opportunity $entity
     */
    protected function onSuccess(Opportunity $entity)
    {
        $this->manager->persist($entity);
    }
}
