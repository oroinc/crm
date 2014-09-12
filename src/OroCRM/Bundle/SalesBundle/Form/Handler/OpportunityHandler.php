<?php

namespace OroCRM\Bundle\SalesBundle\Form\Handler;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

use Doctrine\Common\Persistence\ObjectManager;

use OroCRM\Bundle\SalesBundle\Entity\Opportunity;
use OroCRM\Bundle\ChannelBundle\Provider\RequestChannelProvider;

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

    /**
     * @param FormInterface          $form
     * @param Request                $request
     * @param ObjectManager          $manager
     * @param RequestChannelProvider $requestChannelProvider
     */
    public function __construct(
        FormInterface $form,
        Request $request,
        ObjectManager $manager,
        RequestChannelProvider $requestChannelProvider
    ) {
        $this->form                   = $form;
        $this->request                = $request;
        $this->manager                = $manager;
        $this->requestChannelProvider = $requestChannelProvider;
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
            $this->form->submit($this->request);

            if ($this->form->isValid()) {
                $this->onSuccess($entity);

                return true;
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
