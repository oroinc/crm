<?php

namespace OroCRM\Bundle\SalesBundle\Form\Handler;

use Doctrine\ORM\EntityManagerInterface;

use OroCRM\Bundle\SalesBundle\Entity\B2bCustomer;
use OroCRM\Bundle\SalesBundle\Entity\B2bCustomerEmail;
use OroCRM\Bundle\SalesBundle\Validator\B2bCustomerEmailDeleteValidator;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;
use Oro\Bundle\SecurityBundle\SecurityFacade;

class B2bCustomerEmailHandler
{
    /** @var FormInterface */
    protected $form;

    /** @var Request */
    protected $request;

    /** @var EntityManagerInterface */
    protected $manager;

    /** @var  B2bCustomerEmailDeleteValidator */
    protected $b2bCustomerEmailDeleteValidator;

    /** @var SecurityFacade */
    protected $securityFacade;

    /**
     * @param FormInterface $form
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @param B2bCustomerEmailDeleteValidator $b2bCustomerEmailDeleteValidator
     * @param SecurityFacade $securityFacade
     */
    public function __construct(
        FormInterface $form,
        Request $request,
        EntityManagerInterface $manager,
        B2bCustomerEmailDeleteValidator $b2bCustomerEmailDeleteValidator,
        SecurityFacade $securityFacade
    ) {
        $this->form    = $form;
        $this->request = $request;
        $this->manager = $manager;
        $this->b2bCustomerEmailDeleteValidator = $b2bCustomerEmailDeleteValidator;
        $this->securityFacade = $securityFacade;
    }

    /**
     * Process form
     *
     * @param B2bCustomerEmail $entity
     *
     * @return bool True on successful processing, false otherwise
     *
     * @throws AccessDeniedException
     */
    public function process(B2bCustomerEmail $entity)
    {
        $this->form->setData($entity);

        $submitData = [
            'email' => $this->request->request->get('email'),
            'primary' => $this->request->request->get('primary')
        ];

        if (in_array($this->request->getMethod(), ['POST', 'PUT'])) {
            $this->form->submit($submitData);

            if ($this->form->isValid() && $this->request->request->get('b2bCustomerId')) {
                $customer = $this->manager->find(
                    'OroCRMSalesBundle:B2bCustomer',
                    $this->request->request->get('b2bCustomerId')
                );
                if (!$this->securityFacade->isGranted('EDIT', $customer)) {
                    throw new AccessDeniedException();
                }

                if ($customer->getPrimaryEmail() && $this->request->request->get('primary') === true) {
                    return false;
                }

                $this->onSuccess($entity, $customer);

                return true;
            }
        }

        return false;
    }

    /**
     * @param $id
     * @param ApiEntityManager $manager
     * @throws \Exception
     */
    public function handleDelete($id, ApiEntityManager $manager)
    {
        /** @var B2bCustomerEmail $customerEmail */
        $customerEmail = $manager->find($id);
        if (!$this->securityFacade->isGranted('EDIT', $customerEmail->getOwner())) {
            throw new AccessDeniedException();
        }

        if ($this->b2bCustomerEmailDeleteValidator->validate($customerEmail)) {
            $em = $manager->getObjectManager();
            $em->remove($customerEmail);
            $em->flush();
        } else {
            throw new \Exception("oro.b2bcustomer.email.error.delete.more_one", 500);
        }
    }

    /**
     * @param B2bCustomerEmail $entity
     * @param B2bCustomer $customer
     */
    protected function onSuccess(B2bCustomerEmail $entity, B2bCustomer $customer)
    {
        $entity->setOwner($customer);
        $this->manager->persist($entity);
        $this->manager->flush();
    }
}
