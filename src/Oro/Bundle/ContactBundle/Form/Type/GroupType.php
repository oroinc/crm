<?php

namespace Oro\Bundle\ContactBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

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
                'text',
                array(
                    'label' => 'oro.contact.group.label.label',
                    'required' => true,
                )
            )
            ->add(
                'appendContacts',
                'oro_entity_identifier',
                array(
                    'class'    => 'OroContactBundle:Contact',
                    'required' => false,
                    'mapped'   => false,
                    'multiple' => true,
                )
            )
            ->add(
                'removeContacts',
                'oro_entity_identifier',
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
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Oro\Bundle\ContactBundle\Entity\Group',
                'intention'  => 'group',
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
