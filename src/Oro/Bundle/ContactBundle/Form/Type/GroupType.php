<?php

namespace Oro\Bundle\ContactBundle\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\EntityIdentifierType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GroupType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
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
                    'class'    => 'OroContactBundle:Contact',
                    'required' => false,
                    'mapped'   => false,
                    'multiple' => true,
                )
            )
            ->add(
                'removeContacts',
                EntityIdentifierType::class,
                array(
                    'class'    => 'OroContactBundle:Contact',
                    'required' => false,
                    'mapped'   => false,
                    'multiple' => true,
                )
            );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Oro\Bundle\ContactBundle\Entity\Group',
                'csrf_token_id' => 'group',
            )
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
        return 'oro_contact_group';
    }
}
