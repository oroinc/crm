<?php

namespace Oro\Bundle\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserType extends AbstractType
{
    /**
     * @var EventSubscriberInterface
     */
    protected $subscriber;

    public function __construct(EventSubscriberInterface $subscriber)
    {
        $this->subscriber = $subscriber;
    }

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

        if (!$builder->getData()->getId()) {
            $builder
                ->add('plainPassword', 'repeated', array(
                    'type'           => 'password',
                    'required'       => false,
                    'first_options'  => array('label' => 'Password'),
                    'second_options' => array('label' => 'Password again'),
                ));
        }

        $builder->addEventSubscriber($this->subscriber);
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
        return 'oro_user_form';
    }
}