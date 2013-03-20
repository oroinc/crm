<?php

namespace Oro\Bundle\UserBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Doctrine\ORM\EntityRepository;

use Oro\Bundle\FlexibleEntityBundle\Form\Type\FlexibleType;
use Oro\Bundle\FlexibleEntityBundle\Form\Type\FlexibleValueType;
use Oro\Bundle\UserBundle\Form\EventListener\ProfileSubscriber;
use Oro\Bundle\UserBundle\Entity\User;

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
            ->addEventSubscriber(new ProfileSubscriber())
            ->add('username', 'text', array(
                'required'       => true,
            ))
            ->add('email', 'email', array(
                'label'          => 'E-mail',
                'required'       => true,
            ))
            ->add('firstName', 'text', array(
                'label'          => 'First name',
                'required'       => false,
            ))
            ->add('lastName', 'text', array(
                'label'          => 'Last name',
                'required'       => false,
            ))
            ->add('middleName', 'text', array(
                'label'          => 'Middle name',
                'required'       => false,
            ))
            ->add('birthday', 'birthday', array(
                'label'          => 'Date of birth',
                'required'       => false,
                'widget'         => 'single_text',
            ))
            ->add('image', 'file', array(
                'label'          => 'Image',
                'required'       => false,
            ))
            ->add('enabled', 'checkbox', array(
                'required'       => false,
            ))
            ->add('rolesCollection', 'entity', array(
                'label'          => 'Roles',
                'class'          => 'OroUserBundle:Role',
                'property'       => 'label',
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('r')
                        ->where('r.role <> :anon')
                        ->setParameter('anon', User::ROLE_ANONYMOUS);
                },
                'multiple'       => true,
                'expanded'       => true,
                'required'       => true,
            ))
            ->add('groups', 'entity', array(
                'class'          => 'OroUserBundle:Group',
                'property'       => 'name',
                'multiple'       => true,
                'expanded'       => true,
                'required'       => false,
            ))
            ->add('plainPassword', 'repeated', array(
                'type'           => 'password',
                'required'       => true,
                'first_options'  => array('label' => 'Password'),
                'second_options' => array('label' => 'Password again'),
            ));
    }

    /**
     * Add entity fields to form builder
     *
     * @param FormBuilderInterface $builder
     */
    public function addDynamicAttributesFields(FormBuilderInterface $builder)
    {
        $builder->add('attributes', 'collection', array(
            'type'          => new FlexibleValueType($this->valueClass),
            'property_path' => 'values',
            'allow_add'     => true,
            'allow_delete'  => true,
            'by_reference'  => false
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class'        => $this->flexibleClass,
            'intention'         => 'profile',
            'validation_groups' => function(FormInterface $form) {
                return $form->getData() && $form->getData()->getId()
                    ? array('Profile', 'Default')
                    : array('Registration', 'Profile', 'Default');
            },
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
