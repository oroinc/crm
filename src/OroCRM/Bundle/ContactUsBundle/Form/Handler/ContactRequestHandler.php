<?php

namespace OroCRM\Bundle\ContactUsBundle\Form\Handler;

use Doctrine\ORM\EntityManager;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

use OroCRM\Bundle\ContactUsBundle\Entity\ContactRequest;

class ContactRequestHandler
{
    /** @var FormInterface */
    protected $form;

    /** @var Request */
    protected $request;

    /** @var EntityManager */
    protected $em;

    /**
     * @param FormInterface $form
     * @param Request       $request
     * @param EntityManager $em
     */
    public function __construct(FormInterface $form, Request $request, EntityManager $em)
    {
        $this->form    = $form;
        $this->request = $request;
        $this->em      = $em;
    }

    /**
     * Process form
     *
     * @param ContactRequest $entity
     *
     * @return bool  True on successful processing, false otherwise
     */
    public function process(ContactRequest $entity)
    {
        $this->form->setData($entity);

        if (in_array($this->request->getMethod(), array('POST', 'PUT'))) {
            $this->form->submit($this->request);

            if ($this->form->isValid()) {
                $this->em->persist($entity);
                $this->em->flush();

                return true;
            }
        }

        return false;
    }
}
