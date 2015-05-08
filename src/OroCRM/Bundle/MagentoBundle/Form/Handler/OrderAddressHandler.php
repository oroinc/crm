<?php

namespace OroCRM\Bundle\MagentoBundle\Form\Handler;

use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\HttpFoundation\Request;

use Oro\Bundle\OrganizationBundle\Entity\Organization;

use OroCRM\Bundle\MagentoBundle\Entity\OrderAddress;

class OrderAddressHandler
{
    /** @var FormInterface */
    protected $form;

    /** @var Request */
    protected $request;

    /** @var ObjectManager */
    protected $manager;

    /** @var Organization */
    protected $organization;

    /**
     * @param FormInterface            $form
     * @param Request                  $request
     * @param RegistryInterface        $registry
     * @param SecurityContextInterface $security
     */
    public function __construct(
        FormInterface $form,
        Request $request,
        RegistryInterface $registry,
        SecurityContextInterface $security
    ) {
        $this->form         = $form;
        $this->request      = $request;
        $this->manager      = $registry->getManager();
        $this->organization = $security->getToken()->getOrganizationContext();
    }

    /**
     * Process form
     *
     * @param OrderAddress $entity
     *
     * @return bool True on successful processing, false otherwise
     */
    public function process(OrderAddress $entity)
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
     * @param OrderAddress $entity
     */
    protected function onSuccess(OrderAddress $entity)
    {
        if (null === $entity->getOrganization()) {
            $entity->setOrganization($this->organization);
        }

        $this->manager->persist($entity);
        $this->manager->flush();
    }
}
