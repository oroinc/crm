<?php

namespace OroCRM\Bundle\SalesBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\PropertyAccess\PropertyPath;

class SalesFunnelType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array('required' => true, 'label' => 'orocrm.sales.salesfunnel.name.label'))
            ->add(
                'startDate',
                'oro_date',
                array('required' => true, 'label' => 'orocrm.sales.salesfunnel.start_date.label')
            );

        $builder->add(
            'lead',
            'oro_entity_create_or_select',
            array(
                'label' => 'Lead Information',
                'class' => 'OroCRM\Bundle\SalesBundle\Entity\Lead',
                'create_entity_form_type' => 'orocrm_sales_lead',
                'grid_name' => 'sales-funnel-lead-grid',
                'view_widgets' => array(
                    array(
                        'route_name' => 'orocrm_sales_lead_info',
                        'route_parameters' => array(
                            'id' => new PropertyPath('id')
                        ),
                        'grid_row_to_route' => array(
                            'id' => 'id'
                        ),
                        'widget_alias' => 'w1'
                    )
                ),
            )
        );

        $builder->add(
            'opportunity',
            'oro_entity_create_or_select',
            array(
                'label' => 'Opportunity Information',
                'class' => 'OroCRM\Bundle\SalesBundle\Entity\Opportunity',
                'create_entity_form_type' => 'orocrm_sales_opportunity',
                'grid_name' => 'sales-funnel-opportunity-grid',
                'view_widgets' => array(
                    array(
                        'route_name' => 'orocrm_sales_opportunity_info',
                        'route_parameters' => array(
                            'id' => new PropertyPath('id')
                        ),
                        'grid_row_to_route' => array(
                            'id' => 'id'
                        ),
                        'widget_alias' => 'w2'
                    )
                ),
            )
        );
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'OroCRM\Bundle\SalesBundle\Entity\SalesFunnel',
                'cascade_validation' => false,
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'orocrm_sales_funnel';
    }
}
