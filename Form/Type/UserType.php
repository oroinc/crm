<?php

namespace Oro\Bundle\UserBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\FlexibleEntityBundle\Form\Type\FlexibleType;

class UserType extends FlexibleType
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

        if (!$builder->getData() || !$builder->getData()->getId()) {
            $builder
                ->add('plainPassword', 'repeated', array(
                    'type'           => 'password',
                    'required'       => false,
                    'first_options'  => array('label' => 'Password'),
                    'second_options' => array('label' => 'Password again'),
                ));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_user_form';
    }
}