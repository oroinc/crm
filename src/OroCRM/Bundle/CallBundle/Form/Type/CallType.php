<?php

namespace OroCRM\Bundle\CallBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CallType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('owner', null, array('required' => true))
            ->add('relatedAccount', 'orocrm_account_select', array('required' => false))            
            ->add('subject', 'text', array('required' => true))
            ->add('relatedContact', 'orocrm_contact_select', array('required' => false))
            ->add('contactPhoneNumber', null, array('required' => false))
            ->add('phoneNumber', 'text', array('required' => false))            
            ->add('notes', 'text', array('required' => false))
            ->add('callDateTime', 'oro_datetime', array('required' => true))
            ->add('callStatus', null, array('required' => true))
            ->add('duration', 'time', array('required' => false))
            ->add('isOutgoing', 'checkbox', array('required' => true))
            ;
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'OroCRM\Bundle\CallBundle\Entity\Call',
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'orocrm_call_form';
    }
}
