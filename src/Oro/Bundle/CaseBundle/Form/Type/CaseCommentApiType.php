<?php

namespace Oro\Bundle\CaseBundle\Form\Type;

use Oro\Bundle\ContactBundle\Form\Type\ContactSelectType;
use Oro\Bundle\SoapBundle\Form\EventListener\PatchSubscriber;
use Oro\Bundle\UserBundle\Form\Type\UserSelectType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Defines the form type for case comment data input in REST API endpoints.
 */
class CaseCommentApiType extends AbstractType
{
    #[\Override]
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

    #[\Override]
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'csrf_protection' => false,
                'ownership_disabled' => false,
            ]
        );
    }

    public function getName()
    {
        return $this->getBlockPrefix();
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'oro_case_comment_api';
    }

    #[\Override]
    public function getParent(): ?string
    {
        return CaseCommentType::class;
    }
}
