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
        $builder->add(
            'message',
            'textarea',
            [
                'label'     => 'orocrm.case.casecomment.message.label'
            ]
        );
        if ($options['add_public_field']) {
            $builder->add(
                'public',
                'checkbox',
                [
                    'label'     => 'orocrm.case.casecomment.public.label',
                    'required'  => false,
                ]
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'add_public_field'   => false,
                'data_class'         => 'OroCRM\\Bundle\\CaseBundle\\Entity\\CaseComment',
                'intention'          => 'orocrm_case_comment',
                'ownership_disabled' => true,
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
