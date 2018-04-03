<?php

namespace Oro\Bundle\MagentoBundle\Form\Handler;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Oro\Bundle\FormBundle\Form\Handler\RequestHandlerTrait;
use Oro\Bundle\MagentoBundle\Entity\OrderItem;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class OrderItemHandler
{
    use RequestHandlerTrait;

    /** @var FormInterface */
    protected $form;

    /** @var RequestStack */
    protected $requestStack;

    /** @var Registry */
    protected $manager;

    /**
     * @param FormInterface $form
     * @param RequestStack  $requestStack
     * @param Registry      $registry
     */
    public function __construct(FormInterface $form, RequestStack $requestStack, Registry $registry)
    {
        $this->form = $form;
        $this->requestStack = $requestStack;
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

        $request = $this->requestStack->getCurrentRequest();
        if (in_array($request->getMethod(), ['POST', 'PUT'], true)) {
            $this->submitPostPutRequest($this->form, $request);

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
