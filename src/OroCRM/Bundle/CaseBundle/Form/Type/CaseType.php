<?php

namespace OroCRM\Bundle\CaseBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CaseType extends BaseCaseType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder
            ->add(
                'relatedEntity',
                'choice',
                [
                    'label'    => 'orocrm.case.caseentity.related_entity.label',
                    'choices'  => [
                        'relatedCart'        => 'orocrm.case.caseentity.related_cart.label',
                        'relatedContact'     => 'orocrm.case.caseentity.related_contact.label',
                        'relatedCustomer'    => 'orocrm.case.caseentity.related_customer.label',
                        'relatedLead'        => 'orocrm.case.caseentity.related_lead.label',
                        'relatedOpportunity' => 'orocrm.case.caseentity.related_opportunity.label',
                        'relatedOrder'       => 'orocrm.case.caseentity.related_order.label',
                    ],
                    'mapped'   => false,
                    'required' => false
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
