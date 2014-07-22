<?php

namespace OroCRM\Bundle\CaseBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\SoapBundle\Form\EventListener\PatchSubscriber;

class CaseCommentApiType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'updatedBy',
                'oro_user_select',
                [
                    'label'     => 'orocrm.case.casecomment.updated_by.label',
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

        $builder->addEventSubscriber(new PatchSubscriber());
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'csrf_protection' => false,
                'ownership_disabled' => false,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'orocrm_case_comment_api';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'orocrm_case_comment';
    }
}
