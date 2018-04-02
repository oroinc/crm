<?php

namespace Oro\Bundle\CaseBundle\Form\Type;

use Oro\Bundle\ContactBundle\Form\Type\ContactSelectType;
use Oro\Bundle\SoapBundle\Form\EventListener\PatchSubscriber;
use Oro\Bundle\UserBundle\Form\Type\UserSelectType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
                UserSelectType::class,
                [
                    'label'     => 'oro.case.casecomment.updated_by.label',
                    'required'  => false,
                ]
            )
            ->add(
                'contact',
                ContactSelectType::class,
                [
                    'label'     => 'oro.case.casecomment.contact.label',
                    'required'  => false,
                ]
            );

        $builder->addEventSubscriber(new PatchSubscriber());
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
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
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'oro_case_comment_api';
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return CaseCommentType::class;
    }
}
