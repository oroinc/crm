<?php

namespace Oro\Bundle\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
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
            ->add('groups', 'entity', array(
                'class'     => 'OroUserBundle:Group',
                'property'  => 'name',
                'multiple'  => true,
                'required'  => true,
            ))
        ;

        /**
         * @todo Mode to form event listener
         */
        if (!$builder->getData()->getId()) {
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
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oro\Bundle\UserBundle\Entity\User',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'user';
    }
}