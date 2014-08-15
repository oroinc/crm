<?php

namespace OroCRM\Bundle\CampaignBundle\Form\Handler;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;



use OroCRM\Bundle\CampaignBundle\Entity\Campaign;

class CampaignHandler
{
    /** @var FormInterface */
    protected $form;

    /** @var Request */
    protected $request;

    /** @var EntityManager */
    protected $manager;

    /**
     * @param FormInterface $form
     * @param Request       $request
     * @param EntityManager $manager
     */
    public function __construct(FormInterface $form, Request $request, EntityManager $manager)
    {
        $this->form    = $form;
        $this->request = $request;
        $this->manager = $manager;
    }

    /**
     * Process form
     *
     * @param  Campaign $entity
     *
     * @return bool  True on successful processing, false otherwise
     */
    public function process(Campaign $entity)
    {
        $this->form->setData($entity);

        if (in_array($this->request->getMethod(), ['POST', 'PUT'])) {
            $this->form->submit($this->request);

            if ($this->form->isValid()) {
                $this->onSuccess($entity);

                return true;
            }
        }

        return false;
    }

    /**
     * "Success" form handler
     *
     * @param Campaign $entity
     */
    protected function onSuccess(Campaign $entity)
    {
        $this->manager->persist($entity);
        $this->manager->flush();
    }
}
