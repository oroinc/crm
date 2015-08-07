<?php

namespace OroCRM\Bundle\MagentoBundle\Form\Handler;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

use OroCRM\Bundle\MagentoBundle\Entity\CartItem;

class CartItemHandler
{
    /** @var FormInterface */
    protected $form;

    /** @var Request */
    protected $request;

    /** @var RegistryInterface */
    protected $manager;

    /**
     * @param FormInterface     $form
     * @param Request           $request
     * @param RegistryInterface $registry
     */
    public function __construct(FormInterface $form, Request $request, RegistryInterface $registry)
    {
        $this->form    = $form;
        $this->request = $request;
        $this->manager = $registry->getManager();
    }

    /**
     * Process form
     *
     * @param  CartItem $entity
     *
     * @return bool True on successful processing, false otherwise
     */
    public function process(CartItem $entity)
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
     * @param CartItem $entity
     */
    protected function onSuccess(CartItem $entity)
    {
        $this->manager->persist($entity);
        $this->manager->flush();
    }
}
