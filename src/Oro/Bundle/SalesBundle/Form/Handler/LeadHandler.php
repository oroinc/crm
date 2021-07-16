<?php

namespace Oro\Bundle\SalesBundle\Form\Handler;

use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\ChannelBundle\Provider\RequestChannelProvider;
use Oro\Bundle\FormBundle\Form\Handler\RequestHandlerTrait;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class LeadHandler
{
    use RequestHandlerTrait;

    /** @var FormInterface */
    protected $form;

    /** @var RequestStack */
    protected $requestStack;

    /** @var ObjectManager */
    protected $manager;

    /** @var RequestChannelProvider */
    protected $requestChannelProvider;

    public function __construct(
        FormInterface $form,
        RequestStack $requestStack,
        ObjectManager $manager,
        RequestChannelProvider $requestChannelProvider
    ) {
        $this->form                   = $form;
        $this->requestStack           = $requestStack;
        $this->manager                = $manager;
        $this->requestChannelProvider = $requestChannelProvider;
    }

    /**
     * Process form
     *
     * @param  Lead $entity
     *
     * @return bool True on successful processing, false otherwise
     */
    public function process(Lead $entity)
    {
        $this->form->setData($entity);

        $request = $this->requestStack->getCurrentRequest();
        if (in_array($request->getMethod(), ['POST', 'PUT'], true)) {
            $this->submitPostPutRequest($this->form, $request);

            if ($this->form->isValid()) {
                $this->onSuccess($entity);

                return true;
            }
        }

        return false;
    }

    /**
     * "Success" form handler
     */
    protected function onSuccess(Lead $entity)
    {
        $this->manager->persist($entity);
        $this->manager->flush();
    }
}
