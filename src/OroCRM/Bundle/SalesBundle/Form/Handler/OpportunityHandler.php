<?php

namespace OroCRM\Bundle\SalesBundle\Form\Handler;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

use Doctrine\Common\Persistence\ObjectManager;

use OroCRM\Bundle\SalesBundle\Entity\Opportunity;
use OroCRM\Bundle\ChannelBundle\Provider\ChannelFromRequest;

class OpportunityHandler
{
    /** @var FormInterface */
    protected $form;

    /** @var Request */
    protected $request;

    /** @var ObjectManager */
    protected $manager;

    /** @var ChannelFromRequest */
    protected $channelFromRequest;

    /**
     * @param FormInterface      $form
     * @param Request            $request
     * @param ObjectManager      $manager
     * @param ChannelFromRequest $channelFromRequest
     */
    public function __construct(
        FormInterface $form,
        Request $request,
        ObjectManager $manager,
        ChannelFromRequest $channelFromRequest
    ) {
        $this->form               = $form;
        $this->request            = $request;
        $this->manager            = $manager;
        $this->channelFromRequest = $channelFromRequest;
    }

    /**
     * @param  Opportunity $entity
     *
     * @return bool
     */
    public function process(Opportunity $entity)
    {
        $this->channelFromRequest->setDataChannel($this->request, $entity);

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
