<?php

namespace OroCRM\Bundle\CaseBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use OroCRM\Bundle\CaseBundle\Form\EventListener\RelatedEntitySubscriber;

class CaseType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'subject',
                'text',
                [
                    'label'        => 'orocrm.case.caseentity.subject.label'
                ]
            )
            ->add(
                'description',
                'textarea',
                [
                    'label'        => 'orocrm.case.caseentity.description.label',
                    'required'     => false
                ]
            )
            ->add(
                'owner',
                'oro_user_select',
                [
                    'label'        => 'orocrm.case.caseentity.owner.label'
                ]
            )
            ->add(
                'origin',
                'entity',
                [
                    'label'        => 'orocrm.case.caseentity.origin.label',
                    'class'        => 'OroCRMCaseBundle:CaseOrigin',
                ]
            );

        $this->addRelatedItemEntityFields($builder, $options);
        $this->addRelatedCustomerEntityFields($builder, $options);
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    protected function addRelatedItemEntityFields(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'relatedOrder',
                'orocrm_order_select',
                [
                    'label'         => 'orocrm.case.caseentity.related_order.label',
                    'required'      => false,
                ]
            )
            ->add(
                'relatedCart',
                'orocrm_cart_select',
                [
                    'label'         => 'orocrm.case.caseentity.related_cart.label',
                    'required'      => false,
                ]
            )
            ->add(
                'relatedLead',
                'orocrm_sales_lead_select',
                [
                    'label'         => 'orocrm.case.caseentity.related_lead.label',
                    'required'      => false,
                ]
            )
            ->add(
                'relatedOpportunity',
                'orocrm_sales_opportunity_select',
                [
                    'label'         => 'orocrm.case.caseentity.related_opportunity.label',
                    'required'      => false,
                ]
            )
            ->add(
                'relatedItemEntity',
                'choice',
                [
                    'label'         => 'orocrm.case.caseentity.related_item_entity.label',
                    'choices'       => [
                        'relatedCart'        => 'orocrm.case.caseentity.related_cart.label',
                        'relatedOrder'       => 'orocrm.case.caseentity.related_order.label',
                        'relatedLead'        => 'orocrm.case.caseentity.related_lead.label',
                        'relatedOpportunity' => 'orocrm.case.caseentity.related_opportunity.label',
                    ],
                    'mapped'        => false,
                    'required'      => false
                ]
            );

        $builder->addEventSubscriber(
            new RelatedEntitySubscriber(
                'relatedItemEntity',
                ['relatedCart', 'relatedLead', 'relatedOpportunity', 'relatedOrder']
            )
        );
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    protected function addRelatedCustomerEntityFields(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'relatedContact',
                'orocrm_contact_select',
                [
                    'required'      => false,
                    'label'         => 'orocrm.case.caseentity.related_contact.label',
                ]
            )
            ->add(
                'relatedCustomer',
                'orocrm_customer_select',
                [
                    'label'         => 'orocrm.case.caseentity.related_customer.label',
                    'required'      => false,
                ]
            )
            ->add(
                'relatedCustomerEntity',
                'choice',
                [
                    'label'         => 'orocrm.case.caseentity.related_customer_entity.label',
                    'choices'       => [
                        'relatedContact'     => 'orocrm.case.caseentity.related_contact.label',
                        'relatedCustomer'    => 'orocrm.case.caseentity.related_customer.label',
                    ],
                    'mapped'        => false,
                    'required'      => false
                ]
            );

        $builder->addEventSubscriber(
            new RelatedEntitySubscriber(
                'relatedCustomerEntity',
                ['relatedContact', 'relatedCustomer']
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'         => 'OroCRM\\Bundle\\CaseBundle\\Entity\\CaseEntity',
                'intention'          => 'case',
                'cascade_validation' => true
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'orocrm_case';
    }
}
