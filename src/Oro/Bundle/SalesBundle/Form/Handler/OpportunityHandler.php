<?php

namespace Oro\Bundle\SalesBundle\Form\Handler;

use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\ChannelBundle\Provider\RequestChannelProvider;
use Oro\Bundle\FormBundle\Form\Handler\RequestHandlerTrait;
use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class OpportunityHandler
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

    /** @var LoggerInterface  */
    protected $logger;

    public function __construct(
        FormInterface $form,
        RequestStack $requestStack,
        ObjectManager $manager,
        RequestChannelProvider $requestChannelProvider,
        LoggerInterface $logger
    ) {
        $this->form                   = $form;
        $this->requestStack           = $requestStack;
        $this->manager                = $manager;
        $this->requestChannelProvider = $requestChannelProvider;
        $this->logger                 = $logger;
    }

    /**
     * @param  Opportunity $entity
     *
     * @return bool
     */
    public function process(Opportunity $entity)
    {
        $this->form->setData($entity);

        $request = $this->requestStack->getCurrentRequest();
        if (in_array($request->getMethod(), ['POST', 'PUT'], true)) {
            try {
                $this->submitPostPutRequest($this->form, $request);

                if ($this->form->isValid()) {
                    $this->onSuccess($entity);

                    return true;
                }
            } catch (\Exception $e) {
                $this->logger->error('Email sending failed.', ['exception' => $e]);
                $this->form->addError(new FormError($e->getMessage()));
            }
        }

        return false;
    }

    protected function onSuccess(Opportunity $entity)
    {
        $this->manager->persist($entity);
        $this->manager->flush();
    }
}
