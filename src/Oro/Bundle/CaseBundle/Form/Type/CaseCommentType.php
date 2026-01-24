<?php

namespace Oro\Bundle\CaseBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Defines the form type for case comment data input.
 */
class CaseCommentType extends AbstractType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'message',
            TextareaType::class,
            [
                'label'     => 'oro.case.casecomment.message.label'
            ]
        );

        $builder->add(
            'public',
            CheckboxType::class,
            [
                'label'     => 'oro.case.casecomment.public.label',
                'required'  => false,
            ]
        );
    }

    #[\Override]
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        if (isset($view->vars['public_field_hidden'])) {
            $publicFieldHidden = $view->vars['public_field_hidden'];
        } else {
            $publicFieldHidden = $options['public_field_hidden'];
        }

        if ($publicFieldHidden) {
            unset($view['public']);
        }
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'public_field_hidden'   => true,
                'data_class'            => 'Oro\\Bundle\\CaseBundle\\Entity\\CaseComment',
                'csrf_token_id'         => 'oro_case_comment',
                'ownership_disabled'    => true,
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
        return 'oro_case_comment';
    }
}
