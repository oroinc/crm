<?php

namespace OroCRM\Bundle\SalesBundle\Form\Handler;

use Oro\Bundle\TagBundle\Entity\TagManager;

use OroCRM\Bundle\ChannelBundle\Handler\ChannelFromRequest;
use OroCRM\Bundle\SalesBundle\Entity\B2bCustomer;
use OroCRM\Bundle\SalesBundle\Entity\Lead;
use OroCRM\Bundle\SalesBundle\Entity\Opportunity;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

use Doctrine\Common\Persistence\ObjectManager;

class B2bCustomerHandler
{
    /**
     * @var FormInterface
     */
    protected $form;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var ObjectManager
     */
    protected $manager;

    /**
     * @var TagManager
     */
    protected $tagManager;

    /** @var ChannelFromRequest  */
    protected $channelFromRequest;

    /**
     * @param FormInterface      $form
     * @param Request            $request
     * @param ObjectManager      $manager
     * @param ChannelFromRequest $channelFromRequest
     */
    public function __construct(
        FormInterface $form,
        Request $request,
        ObjectManager $manager,
        ChannelFromRequest $channelFromRequest
    ) {
        $this->form               = $form;
        $this->request            = $request;
        $this->manager            = $manager;
        $this->channelFromRequest = $channelFromRequest;
    }

    /**
     * Process form
     *
     * @param  B2bCustomer $entity
     *
     * @return bool        True on successful processing, false otherwise
     */
    public function process(B2bCustomer $entity)
    {
        $this->channelFromRequest->setDataChannel($this->request, $entity);

        $this->form->setData($entity);

        if (in_array($this->request->getMethod(), array('POST', 'PUT'))) {
            $this->form->submit($this->request);
            $this->handleLeads($entity);
            $this->handleOpportunities($entity);
            if ($this->form->isValid()) {
                $this->onSuccess($entity);

                return true;
            }
        }

        return false;
    }

    /**
     * "Success" form handler
     *
     * @param B2bCustomer $entity
     */
    protected function onSuccess(B2bCustomer $entity)
    {
        $this->manager->persist($entity);
        $this->manager->flush();
        $this->tagManager->saveTagging($entity);
    }

    /**
     * @param B2bCustomer $entity
     */
    protected function handleLeads(B2bCustomer $entity)
    {
        if ($this->form->has('leads')) {
            $leads = $this->form->get('leads');
            $this->appendLeads($entity, $leads->get('added')->getData());
            $this->removeLeads($entity, $leads->get('removed')->getData());
        }
    }

    /**
     * @param B2bCustomer $entity
     */
    protected function handleOpportunities(B2bCustomer $entity)
    {
        if ($this->form->has('opportunities')) {
            $opportunities = $this->form->get('opportunities');
            $this->appendOpportunities($entity, $opportunities->get('added')->getData());
            $this->removeOpportunities($entity, $opportunities->get('removed')->getData());
        }
    }

    /**
     * Append leads to B2bCustomer
     *
     * @param B2bCustomer $b2bCustomer
     * @param Lead[]      $leads
     */
    protected function appendLeads(B2bCustomer $b2bCustomer, array $leads)
    {
        foreach ($leads as $lead) {
            $b2bCustomer->addLead($lead);
        }
    }

    /**
     * Remove leads from B2bCustomer
     *
     * @param B2bCustomer $b2bCustomer
     * @param Lead[]      $leads
     */
    protected function removeLeads(B2bCustomer $b2bCustomer, array $leads)
    {
        foreach ($leads as $lead) {
            $b2bCustomer->removeLead($lead);
        }
    }

    /**
     * Append opportunities to B2bCustomer
     *
     * @param B2bCustomer   $b2bCustomer
     * @param Opportunity[] $opportunities
     */
    protected function appendOpportunities(B2bCustomer $b2bCustomer, array $opportunities)
    {
        foreach ($opportunities as $opportunity) {
            $b2bCustomer->addOpportunity($opportunity);
        }
    }

    /**
     * Remove opportunities from B2bCustomer
     *
     * @param B2bCustomer   $b2bCustomer
     * @param Opportunity[] $opportunities
     */
    protected function removeOpportunities(B2bCustomer $b2bCustomer, array $opportunities)
    {
        foreach ($opportunities as $opportunity) {
            $b2bCustomer->removeOpportunity($opportunity);
        }
    }

    /**
     * Setter for tag manager
     *
     * @param TagManager $tagManager
     */
    public function setTagManager(TagManager $tagManager)
    {
        $this->tagManager = $tagManager;
    }
}
