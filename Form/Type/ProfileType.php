<?php

namespace Oro\Bundle\UserBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

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
                'required'       => true,
            ))
            ->add('email', 'email', array(
                'label'          => 'E-mail',
                'required'       => true,
            ))
            ->add('enabled', 'checkbox', array(
                'required'       => false,
            ))
            ->add('rolesCollection', 'entity', array(
                'label'          => 'Roles',
                'class'          => 'OroUserBundle:Role',
                'property'       => 'label',
                'multiple'       => true,
                'required'       => true,
            ))
            ->add('groups', 'entity', array(
                'class'          => 'OroUserBundle:Group',
                'property'       => 'name',
                'multiple'       => true,
                'required'       => false,
            ))
            ->add('plainPassword', 'repeated', array(
                'type'           => 'password',
                'required'       => false,
                'first_options'  => array('label' => 'Password'),
                'second_options' => array('label' => 'Password again'),
            ));

        $factory = $builder->getFormFactory();

        // leave password only for "Edit user" form
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($factory) {
            if ($event->getData() && $event->getData()->getId()) {
                $event->getForm()->remove('plainPassword');
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => $this->flexibleClass,
            'intention'  => 'profile',
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