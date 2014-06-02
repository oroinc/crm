<?php

namespace OroCRM\Bundle\CaseBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use OroCRM\Bundle\CaseBundle\Entity\CaseOrigin;

class CaseOriginType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'code',
                'choice',
                [
                    'label'   => 'orocrm.case.origin.type.label',
                    'choices' => [
                        CaseOrigin::CODE_EMAIL => 'orocrm.case.origin.type.email',
                        CaseOrigin::CODE_PHONE => 'orocrm.case.origin.type.phone',
                        CaseOrigin::CODE_WEB   => 'orocrm.case.origin.type.web',
                        CaseOrigin::CODE_OTHER => 'orocrm.case.origin.type.other',
                    ]
                ]
            )
            ->add(
                'value',
                'text',
                [
                    'required' => false,
                    'label'    => 'orocrm.case.origin.value.label',
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
                'data_class'         => 'OroCRM\Bundle\CaseBundle\Entity\CaseOrigin',
                'intention'          => 'case_origin',
                'cascade_validation' => true
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'orocrm_case_origin';
    }
}
