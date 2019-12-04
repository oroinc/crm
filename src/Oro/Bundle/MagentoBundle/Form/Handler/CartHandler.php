<?php

namespace Oro\Bundle\MagentoBundle\Form\Handler;

use Doctrine\Common\Persistence\ManagerRegistry;
use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Oro\Bundle\FormBundle\Form\Handler\RequestHandlerTrait;
use Oro\Bundle\MagentoBundle\Entity\Cart;
use Oro\Bundle\MagentoBundle\Entity\CartItem;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class CartHandler
{
    use RequestHandlerTrait;

    /** @var FormInterface */
    protected $form;

    /** @var RequestStack */
    protected $requestStack;

    /** @var ManagerRegistry */
    protected $manager;

    /**
     * @param FormInterface          $form
     * @param RequestStack           $requestStack
     * @param ManagerRegistry        $registry
     * @param TokenAccessorInterface $security
     */
    public function __construct(
        FormInterface $form,
        RequestStack $requestStack,
        ManagerRegistry $registry,
        TokenAccessorInterface $security
    ) {
        $this->form = $form;
        $this->requestStack = $requestStack;
        $this->manager = $registry->getManager();
        $this->organization = $security->getOrganization();
    }

    /**
     * Process form
     *
     * @param  Cart $entity
     *
     * @return bool True on successful processing, false otherwise
     */
    public function process(Cart $entity)
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
     * @param Cart $entity
     */
    protected function onSuccess(Cart $entity)
    {
        $count = 0;
        /** @var CartItem $item */
        foreach ($entity->getCartItems() as $item) {
            $item->setCart($entity);
            ++$count;
        }

        $entity->setItemsCount($count);

        if (null === $entity->getOrganization()) {
            $entity->setOrganization($this->organization);
        }

        if ($entity->getShippingAddress() instanceof AbstractAddress
            && null === $entity->getShippingAddress()->getOrganization()
        ) {
            $entity->getShippingAddress()->setOrganization($this->organization);
        }

        if ($entity->getBillingAddress() instanceof AbstractAddress
            && null === $entity->getBillingAddress()->getOrganization()
        ) {
            $entity->getBillingAddress()->setOrganization($this->organization);
        }

        $this->manager->persist($entity);
        $this->manager->flush();
    }
}
