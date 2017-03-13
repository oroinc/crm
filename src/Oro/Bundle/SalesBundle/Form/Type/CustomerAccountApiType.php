<?php

namespace Oro\Bundle\SalesBundle\Form\Type;

use Oro\Bundle\SalesBundle\Entity\Manager\AccountCustomerManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomerAccountApiType extends AbstractType
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
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $parentForm = $event->getForm()->getParent();

            $account = $parentForm->get('account')->getData();
            $customer = $parentForm->has('customer') && $parentForm->get('customer')->isSubmitted()
                ? $parentForm->get('customer')->getData()
                : null;

            if (!$account && !$customer) {
                $parentForm->getData()->setCustomerAssociation(null);
                return;
            }

            if (!$customer) {
                $parentForm->getData()->setCustomerAssociation(
                    AccountCustomerManager::createCustomer($account, $customer)
                );
            }

            if ($customer &&
                $parentForm->get('customer')->isSubmitted()
            ) {
                $customerAccount = $customerAssociation = $parentForm->getData()
                    ->getCustomerAssociation()
                    ->getAccount();

                if ($customerAccount && $account && $customerAccount->getId() !== $account->getId()) {
                    $parentForm->get('account')->addError(
                        new FormError('This account is invalid for given customer.')
                    );
                    return;
                }
            }
        });
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
        return 'oro_sales_customer_account_api';
    }
}
