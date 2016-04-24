<?php

namespace OroCRM\Bundle\SalesBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use OroCRM\Bundle\SalesBundle\Entity\Opportunity;

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
            'translatable_entity',
            array(
                'label' => 'orocrm.sales.opportunity.close_reason.label',
                'class' => 'OroCRMSalesBundle:OpportunityCloseReason',
                'property' => 'label',
                'required' => false,
                'disabled' => false,
                'empty_value' => 'orocrm.sales.form.choose_close_rsn'
            )
        );

        $builder
            ->add(
                'contact',
                'orocrm_contact_select',
                array('required' => false, 'label' => 'orocrm.sales.opportunity.contact.label')
            )
            ->add(
                'customer',
                'orocrm_sales_b2bcustomer_with_channel_select',
                array('required' => true, 'label' => 'orocrm.sales.opportunity.customer.label')
            )
            ->add('name', 'text', array('required' => true, 'label' => 'orocrm.sales.opportunity.name.label'))
            ->add(
                'dataChannel',
                'orocrm_channel_select_type',
                array(
                    'required' => false,
                    'label' => 'orocrm.sales.opportunity.data_channel.label',
                    'entities' => [
                        'OroCRM\\Bundle\\SalesBundle\\Entity\\Opportunity'
                    ],
                )
            )
            ->add(
                'closeDate',
                'oro_date',
                array('required' => false, 'label' => 'orocrm.sales.opportunity.close_date.label')
            )
            ->add(
                'probability',
                'oro_percent',
                array('required' => false, 'label' => 'orocrm.sales.opportunity.probability.label')
            )
            ->add(
                'budgetAmount',
                'oro_money',
                array('required' => false, 'label' => 'orocrm.sales.opportunity.budget_amount.label')
            )
            ->add(
                'closeRevenue',
                'oro_money',
                array('required' => false, 'label' => 'orocrm.sales.opportunity.close_revenue.label')
            )
            ->add(
                'customerNeed',
                'textarea',
                array('required' => false, 'label' => 'orocrm.sales.opportunity.customer_need.label')
            )
            ->add(
                'proposedSolution',
                'textarea',
                array('required' => false, 'label' => 'orocrm.sales.opportunity.proposed_solution.label')
            )
            ->add(
                'notes',
                'textarea',
                array('required' => false, 'label' => 'orocrm.sales.opportunity.notes.label')
            )
            ->add(
                'state',
                'oro_enum_select',
                array(
                    'required' => false,
                    'label' => 'orocrm.sales.opportunity.state.label',
                    'enum_code' => Opportunity::INTERNAL_STATE_CODE
                )
            );
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'OroCRM\Bundle\SalesBundle\Entity\Opportunity',
                'intention'  => 'opportunity'
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
