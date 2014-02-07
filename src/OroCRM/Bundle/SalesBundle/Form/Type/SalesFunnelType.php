<?php

namespace OroCRM\Bundle\SalesBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SalesFunnelType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array('required' => true, 'label' => 'orocrm.sales.funnel.name.label'))
            ->add(
                'startDate',
                'oro_date',
                array('required' => true, 'label' => 'orocrm.sales.funnel.start_date.label')
            )
            ->add(
                'lead',
                'orocrm_sales_lead_select',
                array(
                    'label' => 'orocrm.sales.funnel.lead.label',
                    'configs' => array(
                        'placeholder' => 'orocrm.sales.form.choose_lead',
                        'extra_config' => 'grid',
                        'grid' => array(
                            'name' => 'sales-lead-grid'
                        ),
                        'minimumInputLength' => 0,
                        'properties' => array('name'),
                    )
                )
            )
            ->add(
                'opportunity',
                'orocrm_sales_opportunity_select',
                array('label' => 'orocrm.sales.funnel.opportunity.label')
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
