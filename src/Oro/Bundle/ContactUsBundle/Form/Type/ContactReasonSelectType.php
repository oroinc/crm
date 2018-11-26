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
    const NAME = 'oro_contactus_contact_reason_select';

    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return OroEntitySelectOrCreateInlineType::class;
    }
}
