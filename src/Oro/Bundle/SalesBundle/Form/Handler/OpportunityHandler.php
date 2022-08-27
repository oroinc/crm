<?php

namespace Oro\Bundle\SalesBundle\Form\Handler;

use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\ChannelBundle\Provider\RequestChannelProvider;
use Oro\Bundle\FormBundle\Form\Handler\FormHandlerInterface;
use Oro\Bundle\FormBundle\Form\Handler\RequestHandlerTrait;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * The handler for Opportunity form.
 */
class OpportunityHandler implements FormHandlerInterface
{
    use RequestHandlerTrait;

    protected ObjectManager $manager;
    protected RequestChannelProvider $requestChannelProvider;
    protected LoggerInterface $logger;

    public function __construct(
        ObjectManager $manager,
        RequestChannelProvider $requestChannelProvider,
        LoggerInterface $logger
    ) {
        $this->manager                = $manager;
        $this->requestChannelProvider = $requestChannelProvider;
        $this->logger                 = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function process($entity, FormInterface $form, Request $request)
    {
        $form->setData($entity);
        if (\in_array($request->getMethod(), ['POST', 'PUT'], true)) {
            try {
                $this->submitPostPutRequest($form, $request);

                if ($form->isValid()) {
                    $this->onSuccess($entity);

                    return true;
                }
            } catch (\Exception $e) {
                $this->logger->error('Email sending failed.', ['exception' => $e]);
                $form->addError(new FormError($e->getMessage()));
            }
        }

        return false;
    }

    protected function onSuccess(Opportunity $entity): void
    {
        $this->manager->persist($entity);
        $this->manager->flush();
    }
}
