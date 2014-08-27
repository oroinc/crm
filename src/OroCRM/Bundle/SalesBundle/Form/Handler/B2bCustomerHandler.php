<?php

namespace OroCRM\Bundle\SalesBundle\Form\Handler;

use Oro\Bundle\TagBundle\Entity\TagManager;

use OroCRM\Bundle\SalesBundle\Entity\B2bCustomer;
use OroCRM\Bundle\SalesBundle\Entity\Lead;

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
     * @param FormInterface $form
     * @param Request       $request
     * @param ObjectManager $manager
     */
    public function __construct(FormInterface $form, Request $request, ObjectManager $manager)
    {
        $this->form    = $form;
        $this->request = $request;
        $this->manager = $manager;
    }

    /**
     * Process form
     *
     * @param  B2bCustomer $entity
     * @return bool  True on successful processing, false otherwise
     */
    public function process(B2bCustomer $entity)
    {
        $this->form->setData($entity);

        if (in_array($this->request->getMethod(), array('POST', 'PUT'))) {
            $this->form->submit($this->request);
            $this->handleLeads($entity);
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
    }

    /**
     * @param Lead $entity
     */
    protected function handleLeads($entity)
    {
        if ($this->form->has('leads')) {
            $leads = $this->form->get('leads');
            $this->appendLeads($entity, $leads->get('added')->getData());
            $this->removeLeads($entity, $leads->get('removed')->getData());
        }
    }

    /**
     * Append leads to B2bCustomer
     *
     * @param B2bCustomer $B2bCustomer
     * @param Lead[] $leads
     */
    protected function appendLeads(B2bCustomer $B2bCustomer, array $leads)
    {
        foreach ($leads as $lead) {
            $B2bCustomer->addLead($lead);
        }
    }

    /**
     * Remove leads from B2bCustomer
     *
     * @param B2bCustomer $B2bCustomer
     * @param Lead[] $leads
     */
    protected function removeLeads(B2bCustomer $B2bCustomer, array $leads)
    {
        foreach ($leads as $lead) {
            $B2bCustomer->removeLead($lead);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setTagManager(TagManager $tagManager)
    {
        $this->tagManager = $tagManager;
    }
}
