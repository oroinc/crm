<?php
namespace OroCRM\Bundle\SalesBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class OpportunitySelectType extends AbstractType
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'configs' => array(
                    'placeholder' => 'orocrm.sales.form.choose_opportunity'
                ),
                'autocomplete_alias' => 'opportunities',
                'grid_name' => 'sales-opportunity-grid',
                'create_form_route' => 'orocrm_sales_opportunity_create'
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
        return 'orocrm_sales_opportunity_select';
    }
}
