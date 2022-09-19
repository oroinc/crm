<?php

namespace Oro\Bundle\SalesBundle\Form\Handler;

use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\ChannelBundle\Provider\RequestChannelProvider;
use Oro\Bundle\FormBundle\Form\Handler\FormHandlerInterface;
use Oro\Bundle\FormBundle\Form\Handler\RequestHandlerTrait;
use Oro\Bundle\SalesBundle\Entity\Lead;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * The handler for Lead form.
 */
class LeadHandler implements FormHandlerInterface
{
    use RequestHandlerTrait;

    protected ObjectManager $manager;
    protected RequestChannelProvider $requestChannelProvider;

    public function __construct(ObjectManager $manager, RequestChannelProvider $requestChannelProvider)
    {
        $this->manager                = $manager;
        $this->requestChannelProvider = $requestChannelProvider;
    }

    /**
     * {@inheritDoc}
     */
    public function process($entity, FormInterface $form, Request $request)
    {
        $form->setData($entity);
        if (\in_array($request->getMethod(), ['POST', 'PUT'], true)) {
            $this->submitPostPutRequest($form, $request);

            if ($form->isValid()) {
                $this->onSuccess($entity);

                return true;
            }
        }

        return false;
    }

    /**
     * "Success" form handler
     */
    protected function onSuccess(Lead $entity): void
    {
        $this->manager->persist($entity);
        $this->manager->flush();
    }
}
