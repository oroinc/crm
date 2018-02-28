<?php

namespace Oro\Bundle\MagentoBundle\Form\Handler;

use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\MagentoBundle\Entity\Customer;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\SalesBundle\Entity\Manager\AccountCustomerManager;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class CustomerApiHandler
{
    /** @var FormInterface */
    protected $form;

    /** @var RequestStack */
    protected $requestStack;

    /** @var ObjectManager */
    protected $manager;

    /** @var AccountCustomerManager */
    protected $accountCustomerManager;

    /** @var Organization */
    protected $organization;

    /**
     * @param FormInterface           $form
     * @param RequestStack            $requestStack
     * @param RegistryInterface       $registry
     * @param TokenAccessorInterface  $security
     * @param AccountCustomerManager  $accountCustomerManager
     */
    public function __construct(
        FormInterface $form,
        RequestStack $requestStack,
        RegistryInterface $registry,
        TokenAccessorInterface $security,
        AccountCustomerManager $accountCustomerManager
    ) {
        $this->form = $form;
        $this->requestStack = $requestStack;
        $this->manager = $registry->getManager();
        $this->organization = $security->getOrganization();
        $this->accountCustomerManager = $accountCustomerManager;
    }

    /**
     * Process form
     *
     * @param  Customer $entity
     *
     * @return bool True on successful processing, false otherwise
     */
    public function process(Customer $entity)
    {
        $this->form->setData($entity);

        $request = $this->requestStack->getCurrentRequest();
        if (in_array($request->getMethod(), ['POST', 'PUT'], true)) {
            $this->form->submit($request);

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
     * @param Customer $entity
     */
    protected function onSuccess(Customer $entity)
    {
        if (null === $entity->getOrganization()) {
            $entity->setOrganization($this->organization);
        }

        $addresses = $entity->getAddresses();

        foreach ($addresses as $address) {
            if (null === $address->getOrganization()) {
                $address->setOrganization($this->organization);
            }
        }

        if ($entity->getId()) {
            $customerAssociation = $this->accountCustomerManager->getAccountCustomerByTarget($entity);
            $customerAssociation->setTarget($entity->getAccount(), $entity);
        } else {
            $customerAssociation = AccountCustomerManager::createCustomer($entity->getAccount(), $entity);
        }

        $this->manager->persist($entity);
        $this->manager->persist($customerAssociation);
        $this->manager->flush();
    }
}
