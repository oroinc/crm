<?php

namespace Oro\Bundle\ContactBundle\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\OroEntitySelectOrCreateInlineType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for Contact select.
 */
class ContactSelectType extends AbstractType
{
    #[\Override]
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'autocomplete_alias' => 'contacts',
                'create_form_route'  => 'oro_contact_create',
                'configs'            => [
                    'placeholder'             => 'oro.contact.form.choose_contact',
                    'result_template_twig'    => '@OroForm/Autocomplete/fullName/result.html.twig',
                    'selection_template_twig' => '@OroForm/Autocomplete/fullName/selection.html.twig'
                ],
            ]
        );
    }

    #[\Override]
    public function getParent(): ?string
    {
        return OroEntitySelectOrCreateInlineType::class;
    }

    public function getName()
    {
        return $this->getBlockPrefix();
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'oro_contact_select';
    }
}
