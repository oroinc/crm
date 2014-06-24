<?php
namespace OroCRM\Bundle\SalesBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class LeadSelectType extends AbstractType
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'configs' => array(
                    'placeholder' => 'orocrm.sales.form.choose_lead'
                ),
                'autocomplete_alias' => 'leads',
                'grid_name' => 'sales-lead-grid',
                'create_form_route' => 'orocrm_sales_lead_create'
            )
        );
    }

    public function getParent()
    {
        return 'oro_entity_create_or_select_inline';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'orocrm_sales_lead_select';
    }
}
