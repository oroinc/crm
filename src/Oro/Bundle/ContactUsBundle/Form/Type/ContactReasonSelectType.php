<?php

namespace Oro\Bundle\ContactUsBundle\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\OroEntitySelectOrCreateInlineType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Represents ContactReason choice type
 */
class ContactReasonSelectType extends AbstractType
{
    public const NAME = 'oro_contactus_contact_reason_select';

    #[\Override]
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'autocomplete_alias' => 'contact_reasons',
            'create_form_route' => 'oro_contactus_reason_create',
            'configs' => [
                'placeholder' => 'oro.contactus.form.choose_contact_reason',
            ]
        ]);
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return self::NAME;
    }

    #[\Override]
    public function getParent(): ?string
    {
        return OroEntitySelectOrCreateInlineType::class;
    }
}
