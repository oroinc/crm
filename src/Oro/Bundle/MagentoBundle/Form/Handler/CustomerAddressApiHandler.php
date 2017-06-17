<?php

namespace Oro\Bundle\MagentoBundle\Form\Handler;

use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\MagentoBundle\Entity\Address;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;

class CustomerAddressApiHandler
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
     * @param FormInterface          $form
     * @param Request                $request
     * @param RegistryInterface      $registry
     * @param TokenAccessorInterface $security
     */
    public function __construct(
        FormInterface $form,
        Request $request,
        RegistryInterface $registry,
        TokenAccessorInterface $security
    ) {
        $this->form         = $form;
        $this->request      = $request;
        $this->manager      = $registry->getManager();
        $this->organization = $security->getOrganization();
    }

    /**
     * Process form
     *
     * @param Address $entity
     *
     * @return bool True on successful processing, false otherwise
     */
    public function process(Address $entity)
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
     * @param Address $entity
     */
    protected function onSuccess(Address $entity)
    {
        if (null === $entity->getOrganization()) {
            $entity->setOrganization($this->organization);
        }

        $this->manager->persist($entity);
        $this->manager->flush();
    }
}
