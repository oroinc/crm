<?php

namespace OroCRM\Bundle\MagentoBundle\Form\Handler;

use Doctrine\Bundle\DoctrineBundle\Registry;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

use OroCRM\Bundle\MagentoBundle\Entity\OrderItem;

class OrderItemHandler
{
    /** @var FormInterface */
    protected $form;

    /** @var Request */
    protected $request;

    /** @var Registry */
    protected $manager;

    /**
     * @param FormInterface $form
     * @param Request       $request
     * @param Registry      $registry
     */
    public function __construct(FormInterface $form, Request $request, Registry $registry)
    {
        $this->form    = $form;
        $this->request = $request;
        $this->manager = $registry->getManager();
    }

    /**
     * Process form
     *
     * @param  OrderItem $entity
     *
     * @return bool True on successful processing, false otherwise
     */
    public function process(OrderItem $entity)
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
     * @param OrderItem $entity
     */
    protected function onSuccess(OrderItem $entity)
    {
        $this->manager->persist($entity);
        $this->manager->flush();
    }
}
