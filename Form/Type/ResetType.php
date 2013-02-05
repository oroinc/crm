<?php

namespace Oro\Bundle\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ResetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('plainPassword', 'repeated', array(
            'type'            => 'password',
            'required'        => true,
            'first_options'   => array('label' => 'Password'),
            'second_options'  => array('label' => 'Password again'),
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Oro\Bundle\UserBundle\Entity\User',
            'intention'  => 'reset',
        ));
    }

    public function getName()
    {
        return 'oro_user_reset';
    }
}
