<?php

namespace Oro\Bundle\SalesBundle\Form\Type;

use Oro\Bundle\SalesBundle\Entity\Manager\AccountCustomerManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomerApiType extends AbstractType
{
    /** @var AccountCustomerManager */
    protected $accountCustomerManager;

    /**
     * @param AccountCustomerManager $accountCustomerManager
     */
    public function __construct(AccountCustomerManager $accountCustomerManager)
    {
        $this->accountCustomerManager = $accountCustomerManager;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) {
                $parentForm = $event->getForm()->getParent();

                if ($parentForm->has('account') && $parentForm->get('account')->isSubmitted()) {
                    throw new \LogicException('"customer" field has to be submitted before "account" field');
                }

                $customer = $parentForm->has('customer') && $parentForm->get('customer')->isSubmitted()
                    ? $parentForm->get('customer')->getData()
                    : null;

                if (!$customer) {
                    $parentForm->getData()->setCustomerAssociation(null);
                    return;
                }

                $customerAssociation = $this->accountCustomerManager->getAccountCustomerByTarget(
                    $customer,
                    false
                );
                if (!$customerAssociation) {
                    $customerAssociation = AccountCustomerManager::createCustomer(
                        $this->accountCustomerManager->createAccountForTarget($customer),
                        $customer
                    );
                }

                $parentForm->getData()->setCustomerAssociation($customerAssociation);
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'mapped' => false,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'oro_api_entity';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'oro_sales_customer_api';
    }
}
