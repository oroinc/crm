<?php

namespace Oro\Bundle\ContactUsBundle\Form\Handler;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ContactUsBundle\Entity\ContactRequest;
use Oro\Bundle\FormBundle\Form\Handler\RequestHandlerTrait;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Handles submit data of ContactRequest form type.
 */
class ContactRequestHandler
{
    use RequestHandlerTrait;

    /** @var FormInterface */
    protected $form;

    /** @var RequestStack */
    protected $requestStack;

    /** @var ManagerRegistry */
    protected $registry;

    public function __construct(FormInterface $form, RequestStack $requestStack, ManagerRegistry $registry)
    {
        $this->form = $form;
        $this->requestStack = $requestStack;
        $this->registry = $registry;
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
            $this->submitPostPutRequest($this->form, $request);

            if ($this->getForm()->isValid()) {
                $manager = $this->registry->getManagerForClass(ContactRequest::class);
                $manager->persist($entity);
                $manager->flush();

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
