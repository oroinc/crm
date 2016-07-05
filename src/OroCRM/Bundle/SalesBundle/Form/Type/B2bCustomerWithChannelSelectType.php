<?php

namespace OroCRM\Bundle\SalesBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class B2bCustomerWithChannelSelectType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'configs'            => [
                    'placeholder'            => 'orocrm.sales.form.choose_b2bcustomer',
                    'properties'             => ['name'],
                    'allowCreateNew'         => true
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
        return 'orocrm_sales_b2bcustomer_with_channel_select';
    }
}
