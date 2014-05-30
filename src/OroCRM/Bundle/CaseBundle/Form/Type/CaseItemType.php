<?php

namespace OroCRM\Bundle\CaseBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CaseItemType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // @todo: identify relations properties
        $builder
            ->add(
                'order',
                'entity',
                [
                    'label'    => 'orocrm.case.item.order.label',
                    'class'    => 'OroCRMMagentoBundle:Order',
                    'property' => 'id',
                    'required' => false,
                ]
            )
            ->add(
                'cart',
                'entity',
                [
                    'label'    => 'orocrm.case.item.cart.label',
                    'class'    => 'OroCRMMagentoBundle:Cart',
                    'property' => 'id',
                    'required' => false,
                ]
            )
            ->add(
                'lead',
                'entity',
                [
                    'label'    => 'orocrm.case.item.lead.label',
                    'class'    => 'OroCRMSalesBundle:Lead',
                    'property' => 'name',
                    'required' => false,
                ]
            )
            ->add(
                'opportunity',
                'entity',
                [
                    'label'    => 'orocrm.case.item.opportunity.label',
                    'class'    => 'OroCRMSalesBundle:Opportunity',
                    'property' => 'name',
                    'required' => false,
                ]
            );
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'         => 'OroCRM\Bundle\CaseBundle\Entity\CaseItem',
                'intention'          => 'case_item',
                'cascade_validation' => true
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'orocrm_case_item';
    }
}
