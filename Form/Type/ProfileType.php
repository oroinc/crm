<?php

namespace Oro\Bundle\UserBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;

use Oro\Bundle\FlexibleEntityBundle\Form\Type\FlexibleType;

class ProfileType extends FlexibleType
{
    /**
     * {@inheritdoc}
     */
    public function addEntityFields(FormBuilderInterface $builder)
    {
        // add default flexible fields
        parent::addEntityFields($builder);

        // user fields
        $builder
            ->add('username', 'text', array(
                'required'  => true,
            ))
            ->add('email', 'email', array(
                'label'     => 'E-mail',
                'required'  => true,
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
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_user_profile';
    }
}