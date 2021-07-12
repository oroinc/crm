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
    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return OroEntitySelectOrCreateInlineType::class;
    }

    /**
     * {@inheritdoc}
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
        return 'oro_contact_select';
    }
}
