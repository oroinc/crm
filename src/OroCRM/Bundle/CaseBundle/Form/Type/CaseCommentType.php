<?php

namespace OroCRM\Bundle\CaseBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CaseCommentType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'body',
                'text',
                [
                    'label'     => 'orocrm.case.casecomment.body.label'
                ]
            )
            ->add(
                'public',
                'checkbox',
                [
                    'label'     => 'orocrm.case.casecomment.public.label',
                    'required'  => false,
                ]
            )
            ->add(
                'contact',
                'orocrm_contact_select',
                [
                    'label'     => 'orocrm.case.casecomment.contact.label',
                    'required'  => false,
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
                'data_class'         => 'OroCRM\\Bundle\\CaseBundle\\Entity\\CaseComment',
                'intention'          => 'orocrm_case_comment',
                'cascade_validation' => true,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'orocrm_case_comment';
    }
}
