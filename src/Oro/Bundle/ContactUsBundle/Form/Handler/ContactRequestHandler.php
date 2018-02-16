<?php

namespace Oro\Bundle\ContactUsBundle\Form\Handler;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\ContactUsBundle\Entity\ContactRequest;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class ContactRequestHandler
{
    /** @var FormInterface */
    protected $form;

    /** @var RequestStack */
    protected $requestStack;

    /** @var EntityManager */
    protected $em;

    /**
     * @param FormInterface $form
     * @param RequestStack  $requestStack
     * @param EntityManager $em
     */
    public function __construct(FormInterface $form, RequestStack $requestStack, EntityManager $em)
    {
        $this->form = $form;
        $this->requestStack = $requestStack;
        $this->em = $em;
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
        $this->getForm()->setData($entity);

        $request = $this->requestStack->getCurrentRequest();
        if (in_array($request->getMethod(), ['POST', 'PUT'], true)) {
            $this->getForm()->submit($request);

            if ($this->getForm()->isValid()) {
                $this->em->persist($entity);
                $this->em->flush();

                return true;
            }
        }

        return false;
    }

    /**
     * @return FormInterface
     */
    public function getForm()
    {
        return $this->form;
    }
}
