<?php

namespace OroCRM\Bundle\SalesBundle\Form\Type;

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
                    'placeholder' => 'orocrm.sales.form.choose_b2bcustomer'
                ),
                'autocomplete_alias' => 'b2b_cusomers',
                'grid_name'          => 'b2bcustomers-select-grid',
                // TODO enable create when controller will be ready
                // 'create_form_route'  => 'orocrm_sales_b2bcustomer_create',
                'create_enabled' => false
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'oro_entity_create_or_select_inline';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'orocrm_sales_b2bcustomer_select';
    }
}
