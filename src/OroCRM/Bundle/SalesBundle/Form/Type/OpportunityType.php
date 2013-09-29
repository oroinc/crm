<?php

namespace OroCRM\Bundle\SalesBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\AbstractType;

class OpportunityType extends AbstractType
{
    const NAME = 'orocrm_sales_opportunity';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'closeReason',
            'entity',
            array(
                'class'       => 'OroCRMSalesBundle:OpportunityCloseReason',
                'property'    => 'label',
                'required'    => false,
                'disabled'    => false,
            )
        );

        $builder
            ->add('contact', 'orocrm_contact_select', array('required' => false))
            ->add('account', 'orocrm_account_select', array('required' => false))
            ->add('name', 'text', array('required' => true))
            ->add('closeDate', 'oro_date', array('required' => false))
            ->add('probability', 'percent', array('required' => false))
            ->add('budgetAmount', 'number', array('required' => false))
            ->add('closeRevenue', 'number', array('required' => false))
            ->add('customerNeed', 'text', array('required' => false))
            ->add('proposedSolution', 'text', array('required' => false));
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'OroCRM\Bundle\SalesBundle\Entity\Opportunity',
                'intention'  => 'opportunity',
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
