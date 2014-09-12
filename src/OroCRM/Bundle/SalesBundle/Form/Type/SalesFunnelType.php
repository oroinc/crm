<?php

namespace OroCRM\Bundle\SalesBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SalesFunnelType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'startDate',
                'oro_date',
                array('required' => true, 'label' => 'orocrm.sales.salesfunnel.start_date.label')
            )
            ->add(
                'dataChannel',
                'orocrm_channel_select_type',
                array(
                    'required' => true,
                    'label' => 'orocrm.sales.salesfunnel.data_channel.label',
                    'entities' => [
                        'OroCRM\\Bundle\\SalesBundle\\Entity\\SalesFunnel'
                    ],
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
