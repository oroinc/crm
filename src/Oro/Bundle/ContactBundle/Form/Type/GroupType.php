<?php

namespace Oro\Bundle\ContactBundle\Form\Type;

use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\FormBundle\Form\Type\EntityIdentifierType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Contact group form type
 */
class GroupType extends AbstractType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'label',
                TextType::class,
                array(
                    'label' => 'oro.contact.group.label.label',
                    'required' => true,
                )
            )
            ->add(
                'appendContacts',
                EntityIdentifierType::class,
                array(
                    'class'    => Contact::class,
                    'required' => false,
                    'mapped'   => false,
                    'multiple' => true,
                )
            )
            ->add(
                'removeContacts',
                EntityIdentifierType::class,
                array(
                    'class'    => Contact::class,
                    'required' => false,
                    'mapped'   => false,
                    'multiple' => true,
                )
            );
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Oro\Bundle\ContactBundle\Entity\Group',
                'csrf_token_id' => 'group',
            )
        );
    }

    public function getName()
    {
        return $this->getBlockPrefix();
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return 'oro_contact_group';
    }
}
