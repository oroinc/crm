<?php

namespace OroCRM\Bundle\CaseBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

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
                    'label' => 'orocrm.case.subject.label'
                ]
            )
            ->add(
                'description',
                'textarea',
                [
                    'label' => 'orocrm.case.description.label'
                ]
            )
            ->add(
                'owner',
                'oro_user_select',
                [
                    'label' => 'orocrm.case.owner.label'
                ]
            )
            ->add(
                'reporter',
                'oro_user_select',
                [
                    'required' => false,
                    'label'    => 'orocrm.case.reporter.user.label',
                ]
            )
            ->add(
                'relatedContact',
                'orocrm_contact_select',
                [
                    'required' => false,
                    'label'    => 'orocrm.case.reporter.contact.label',
                ]
            )
            ->add(
                'relatedCustomer',
                'entity',
                [
                    'label'    => 'orocrm.case.reporter.customer.label',
                    'class'    => 'OroCRMMagentoBundle:Customer',
                    'property' => 'email',
                    'required' => false,
                ]
            )
            ->add(
                'relatedOrder',
                'entity',
                [
                    'label'    => 'orocrm.case.item.order.label',
                    'class'    => 'OroCRMMagentoBundle:Order',
                    'required' => false,
                ]
            )
            ->add(
                'relatedCart',
                'entity',
                [
                    'label'    => 'orocrm.case.item.cart.label',
                    'class'    => 'OroCRMMagentoBundle:Cart',
                    'required' => false,
                ]
            )
            ->add(
                'relatedLead',
                'entity',
                [
                    'label'    => 'orocrm.case.item.lead.label',
                    'class'    => 'OroCRMSalesBundle:Lead',
                    'required' => false,
                ]
            )
            ->add(
                'relatedOpportunity',
                'entity',
                [
                    'label'    => 'orocrm.case.item.opportunity.label',
                    'class'    => 'OroCRMSalesBundle:Opportunity',
                    'required' => false,
                ]
            )
            ->add(
                'origin',
                'entity',
                [
                    'label'    => 'orocrm.case.origins.label',
                    'class'    => 'OroCRMCaseBundle:CaseOrigin',
                    'property' => 'code',
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
