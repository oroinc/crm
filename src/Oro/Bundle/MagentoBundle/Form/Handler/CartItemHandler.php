<?php

namespace Oro\Bundle\MagentoBundle\Form\Handler;

use Oro\Bundle\MagentoBundle\Entity\CartItem;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class CartItemHandler
{
    /** @var FormInterface */
    protected $form;

    /** @var RequestStack */
    protected $requestStack;

    /** @var RegistryInterface */
    protected $manager;

    /**
     * @param FormInterface     $form
     * @param RequestStack      $requestStack
     * @param RegistryInterface $registry
     */
    public function __construct(FormInterface $form, RequestStack $requestStack, RegistryInterface $registry)
    {
        $this->form = $form;
        $this->requestStack = $requestStack;
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

        $request = $this->requestStack->getCurrentRequest();
        if (in_array($request->getMethod(), ['POST', 'PUT'], true)) {
            $this->form->handleRequest($request);

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
