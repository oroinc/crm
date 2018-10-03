<?php

namespace Oro\Bundle\ContactUsBundle\Form\Type;

use Oro\Bundle\ContactUsBundle\Entity\ContactReason;
use Oro\Bundle\LocaleBundle\Form\Type\LocalizedFallbackValueCollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Represents ContactReason type for ContactReason entity with localizable titles
 */
class ContactReasonType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'titles',
                LocalizedFallbackValueCollectionType::class,
                [
                    'property_path' => 'titles',
                    'label' => 'oro.contactus.contactreason.label.label',
                    'required' => true,
                    'entry_options' => ['constraints' => [new NotBlank()]]
                ]
            );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => ContactReason::class
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'oro_contactus_contact_reason';
    }
}
