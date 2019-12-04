<?php

namespace Oro\Bundle\MagentoBundle\Form\Handler;

use Doctrine\Common\Persistence\ManagerRegistry;
use Oro\Bundle\FormBundle\Form\Handler\RequestHandlerTrait;
use Oro\Bundle\MagentoBundle\Entity\Order;
use Oro\Bundle\MagentoBundle\Entity\OrderAddress;
use Oro\Bundle\MagentoBundle\Entity\OrderItem;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class OrderHandler
{
    use RequestHandlerTrait;

    /** @var FormInterface */
    protected $form;

    /** @var RequestStack */
    protected $requestStack;

    /** @var ManagerRegistry */
    protected $manager;

    /** @var Organization */
    protected $organization;

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
     * @param  Order $entity
     *
     * @return bool True on successful processing, false otherwise
     */
    public function process(Order $entity)
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
     * @param Order $entity
     */
    protected function onSuccess(Order $entity)
    {
        if (null === $entity->getOrganization()) {
            $entity->setOrganization($this->organization);
        }

        /** @var OrderAddress $address */
        foreach ($entity->getAddresses() as $address) {
            if (null === $address->getOwner()) {
                $address->setOwner($entity);
            }

            if (null === $address->getOrganization()) {
                $address->setOrganization($this->organization);
            }
        }

        /** @var OrderItem $item */
        foreach ($entity->getItems() as $item) {
            if (null === $item->getOrder()) {
                $item->setOrder($entity);
            }
        }

        $this->manager->persist($entity);
        $this->manager->flush();
    }
}
