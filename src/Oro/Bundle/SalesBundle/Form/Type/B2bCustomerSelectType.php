<?php

namespace Oro\Bundle\SalesBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class B2bCustomerSelectType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'configs'            => array(
                    'placeholder' => 'oro.sales.form.choose_b2bcustomer'
                ),
                'autocomplete_alias' => 'b2b_customers',
                'grid_name'          => 'orocrm-sales-b2bcustomers-select-grid',
                'create_form_route'  => 'oro_sales_b2bcustomer_create',
                'create_enabled'     => true
            )
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
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'oro_sales_b2bcustomer_select';
    }
}
