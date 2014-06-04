<?php

namespace OroCRM\Bundle\CaseBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class BaseCaseType extends AbstractType
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
                    'label' => 'orocrm.case.caseentity.subject.label',
                ]
            )
            ->add(
                'description',
                'textarea',
                [
                    'label'    => 'orocrm.case.caseentity.description.label',
                    'required' => false
                ]
            )
            ->add(
                'owner',
                'oro_user_select',
                [
                    'label' => 'orocrm.case.caseentity.owner.label'
                ]
            )
            ->add(
                'origin',
                'entity',
                [
                    'label' => 'orocrm.case.caseentity.origin.label',
                    'class' => 'OroCRMCaseBundle:CaseOrigin',
                ]
            )
            ->add(
                'relatedContact',
                'orocrm_contact_select',
                [
                    'required' => false,
                    'label'    => 'orocrm.case.caseentity.related_contact.label',
                ]
            )
            ->add(
                'relatedCustomer',
                'entity',
                [
                    'label'    => 'orocrm.case.caseentity.related_customer.label',
                    'class'    => 'OroCRMMagentoBundle:Customer',
                    'property' => 'email',
                    'required' => false,
                ]
            )
            ->add(
                'relatedOrder',
                'entity',
                [
                    'label'    => 'orocrm.case.caseentity.related_order.label',
                    'class'    => 'OroCRMMagentoBundle:Order',
                    'required' => false,
                    'property' => 'incrementId'
                ]
            )
            ->add(
                'relatedCart',
                'entity',
                [
                    'label'    => 'orocrm.case.caseentity.related_cart.label',
                    'class'    => 'OroCRMMagentoBundle:Cart',
                    'required' => false,
                    'property' => 'id'
                ]
            )
            ->add(
                'relatedLead',
                'orocrm_sales_lead_select',
                [
                    'label'    => 'orocrm.case.caseentity.related_lead.label',
                    'required' => false,
                ]
            )
            ->add(
                'relatedOpportunity',
                'orocrm_sales_opportunity_select',
                [
                    'label'    => 'orocrm.case.caseentity.related_opportunity.label',
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
                'data_class'         => 'OroCRM\Bundle\CaseBundle\Entity\CaseEntity',
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
