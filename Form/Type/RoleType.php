<?php

namespace Oro\Bundle\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RoleType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('role', 'text', array(
                'required'  => true,
            ))
            ->add('label', 'text', array(
                'required'  => false,
            ))
            ->add('enabled', 'checkbox', array(
                'required'  => false,
            ))
            ->add('rolesCollection', 'entity', array(
                'label'     => 'Roles',
                'class'     => 'OroUserBundle:Role',
                'property'  => 'label',
                'multiple'  => true,
                'required'  => true,
            ))
            ->add('groups', 'entity', array(
                'class'     => 'OroUserBundle:Group',
                'property'  => 'name',
                'multiple'  => true,
                'required'  => false,
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oro\Bundle\UserBundle\Entity\Role',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_user_role_form';
    }
}