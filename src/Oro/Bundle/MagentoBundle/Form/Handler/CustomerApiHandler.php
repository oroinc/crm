<?php

namespace Oro\Bundle\MagentoBundle\Form\Handler;

use Doctrine\Common\Persistence\ObjectManager;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContextInterface;

use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\MagentoBundle\Entity\Customer;

use Oro\Bundle\SalesBundle\Entity\Manager\AccountCustomerManager;

class CustomerApiHandler
{
    /** @var FormInterface */
    protected $form;

    /** @var Request */
    protected $request;

    /** @var ObjectManager */
    protected $manager;

    /** @var AccountCustomerManager */
    protected $accountCustomerManager;

    /** @var Organization */
    protected $organization;

    /**
     * @param FormInterface            $form
     * @param Request                  $request
     * @param RegistryInterface        $registry
     * @param SecurityContextInterface $security
     * @param AccountCustomerManager   $accountCustomerManager
     */
    public function __construct(
        FormInterface $form,
        Request $request,
        RegistryInterface $registry,
        SecurityContextInterface $security,
        AccountCustomerManager $accountCustomerManager
    ) {
        $this->form = $form;
        $this->request = $request;
        $this->manager = $registry->getManager();
        $this->organization = $security->getToken()->getOrganizationContext();
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
