<?php

namespace OroCRM\Bundle\SalesBundle\Form\Handler;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

use Doctrine\Common\Persistence\ObjectManager;

use OroCRM\Bundle\SalesBundle\Entity\SalesFlowOpportunity;

class SalesFlowOpportunityHandler
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
     * @param Request       $request
     * @param ObjectManager $manager
     */
    public function __construct(Request $request, ObjectManager $manager)
    {
        $this->request = $request;
        $this->manager = $manager;
    }

    /**
     * @param FormInterface $form
     * @return SalesFlowOpportunityHandler
     */
    public function setForm(FormInterface $form)
    {
        $this->form = $form;

        return $this;
    }

    /**
     * @return FormInterface
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @return bool
     */
    public function process()
    {
        if (in_array($this->request->getMethod(), array('POST', 'PUT'))) {
            $this->form->submit($this->request);

            if ($this->form->isValid()) {
                $this->onSuccess($this->form->getData());

                return true;
            }
        }

        return false;
    }

    /**
     * @param SalesFlowOpportunity $entity
     */
    protected function onSuccess(SalesFlowOpportunity $entity)
    {
        $this->manager->persist($entity);
        $this->manager->flush();
    }
}
