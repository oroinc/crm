<?php

namespace OroCRM\Bundle\SalesBundle\Form\Handler;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\UIBundle\Route\Router;

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

    /**
     * @var LeadToOpportunityProvider
     */
    protected $leadToOpportunityProvider;

    /** @var RequestChannelProvider */
    protected $router;

    public function __construct(
        FormInterface $form,
        Request $request,
        ObjectManager $manager,
        RequestChannelProvider $requestChannelProvider,
        ChangeLeadStatus $changeLeadStatusModel,
        LeadToOpportunityProvider $leadToOpportunityProvider,
        Router $router
    ) {
        $this->leadToOpportunityProvider = $leadToOpportunityProvider;
        $this->changeLeadStatusModel = $changeLeadStatusModel;
        $this->router = $router;
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
     * @param Lead $lead
     * @param      $createMessage
     *
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function create(Lead $lead, $createMessage)
    {
        $opportunity = $this->leadToOpportunityProvider->prepareOpportunity($lead, $this->request);
        if (!$this->process($opportunity)) {
            return [
                'form'   => $this->form->createView(),
                'entity' => $opportunity
            ];
        }
        $this->get('session')->getFlashBag()->add(
            'success',
            $createMessage
        );
        return $this->router->redirect($opportunity);
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
