<?php

namespace Oro\Bundle\SalesBundle\Form\Type;

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
                array('required' => true, 'label' => 'oro.sales.salesfunnel.start_date.label')
            )
            ->add(
                'dataChannel',
                'oro_channel_select_type',
                array(
                    'required' => true,
                    'label' => 'oro.sales.salesfunnel.data_channel.label',
                    'entities' => [
                        'Oro\\Bundle\\SalesBundle\\Entity\\SalesFunnel'
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
                'data_class' => 'Oro\Bundle\SalesBundle\Entity\SalesFunnel',
                'cascade_validation' => false,
            )
        );
    }

    /**
     * @return string
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
        return 'oro_sales_funnel';
    }
}
