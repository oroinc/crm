<?php

namespace OroCRM\Bundle\SalesBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\SecurityBundle\SecurityFacade;

use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\SalesBundle\Entity\B2bCustomer;

class B2bCustomerWithChannelSelectType extends AbstractType
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
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'configs'            => [
                    'placeholder'             => 'orocrm.sales.form.choose_b2bcustomer',
                    'properties'              => ['name'],
                    'allowCreateNew'          => $this->isGrantedCreateBusinessAccount(),
                ],
                'autocomplete_alias' => 'b2b_customers_with_channel',
                'grid_name'          => 'orocrm-sales-b2bcustomers-select-grid',
                'create_form_route'  => 'orocrm_sales_b2bcustomer_create',
                'create_enabled'     => true,
                'tooltip'            => 'orocrm.sales.form.tooltip.account',
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
        return 'orocrm_sales_b2bcustomer_with_channel_select';
    }
}
