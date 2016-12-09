<?php

namespace Oro\Bundle\SalesBundle\Form\Handler;

use Psr\Log\LoggerInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\SalesBundle\Entity\Opportunity;
use Oro\Bundle\ChannelBundle\Provider\RequestChannelProvider;

class OpportunityHandler
{
    /** @var FormInterface */
    protected $form;

    /** @var Request */
    protected $request;

    /** @var ObjectManager */
    protected $manager;

    /** @var RequestChannelProvider */
    protected $requestChannelProvider;

    /** @var LoggerInterface  */
    protected $logger;

    /**
     * @param FormInterface $form
     * @param Request $request
     * @param ObjectManager $manager
     * @param RequestChannelProvider $requestChannelProvider
     * @param LoggerInterface $logger
     */
    public function __construct(
        FormInterface $form,
        Request $request,
        ObjectManager $manager,
        RequestChannelProvider $requestChannelProvider,
        LoggerInterface $logger
    ) {
        $this->form                   = $form;
        $this->request                = $request;
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
        $this->requestChannelProvider->setDataChannel($entity);

        $this->form->setData($entity);

        if (in_array($this->request->getMethod(), array('POST', 'PUT'))) {
            try {
                $this->form->submit($this->request);

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

    /**
     * @param Opportunity $entity
     */
    protected function onSuccess(Opportunity $entity)
    {
        $this->manager->persist($entity);
        $this->manager->flush();
    }
}
