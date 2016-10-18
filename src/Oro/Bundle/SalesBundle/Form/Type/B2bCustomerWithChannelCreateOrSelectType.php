<?php

namespace Oro\Bundle\SalesBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\SecurityBundle\SecurityFacade;
use Oro\Bundle\AccountBundle\Entity\Account;
use Oro\Bundle\SalesBundle\Entity\B2bCustomer;

class B2bCustomerWithChannelCreateOrSelectType extends AbstractType
{
    /** @var SecurityFacade */
    protected $securityFacade;

    /**
     * @param SecurityFacade $securityFacade
     */
    public function __construct(SecurityFacade $securityFacade)
    {
        $this->securityFacade = $securityFacade;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(
            new CallbackTransformer(
                function ($customer) {
                    return $customer;
                },
                function ($customer) use ($options) {
                    if ($customer instanceof B2bCustomer) {
                        $account = $customer->getAccount();
                        if (!$account && !empty($options['configs']['allowCreateNew'])) {
                            $account = new Account();
                            $account->setName($customer->getName());
                            $customer->setAccount($account);
                        }
                    }

                    return $customer;
                }
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'configs'            => [
                    'placeholder'    => 'oro.sales.form.choose_b2bcustomer',
                    'properties'     => ['name'],
                    'allowCreateNew' => $this->isGrantedCreateBusinessAccount(),
                    'async_dialogs'  => true
                ],
                'autocomplete_alias' => 'b2b_customers_with_channel',
                'grid_name'          => 'orocrm-sales-b2bcustomers-select-grid',
                'create_form_route'  => 'oro_sales_b2bcustomer_create',
                'create_enabled'     => true,
                'tooltip'            => 'oro.sales.form.tooltip.account',
            ]
        );
    }

    /**
     * @return bool
     */
    protected function isGrantedCreateBusinessAccount()
    {
        // New B2bCustomer needs an account, for new customer it will be created automatically
        $isGrantedCreateAccount  = $this->securityFacade->isGranted('CREATE', sprintf('Entity:%s', Account::class));
        $isGrantedCreateCustomer = $this->securityFacade->isGranted('CREATE', sprintf('Entity:%s', B2bCustomer::class));

        return $isGrantedCreateAccount && $isGrantedCreateCustomer;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'oro_entity_create_or_select_inline_channel_aware';
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
        return 'oro_sales_b2bcustomer_with_channel_create_or_select';
    }
}
