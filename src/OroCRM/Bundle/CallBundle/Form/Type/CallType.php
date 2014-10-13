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
            ->add(
                'relatedAccount',
                'orocrm_account_select',
                array('required' => false, 'label' => 'orocrm.call.related_account.label')
            )
            ->add('subject', 'text', array('required' => true, 'label' => 'orocrm.call.subject.label'))
            ->add(
                'relatedContact',
                'orocrm_contact_select',
                array('required' => false, 'label' => 'orocrm.call.related_contact.label')
            )
            ->add(
                'phoneNumber',
                'orocrm_call_phone',
                array(
                    'required' => true,
                    'label' => 'orocrm.call.phone_number.label',
                    'suggestions' => $options['suggestions'],
                    'default_choice' => $options['default_choice'],
                )
            )
            ->add('notes', 'textarea', array('required' => false, 'label' => 'orocrm.call.notes.label'))
            ->add(
                'callDateTime',
                'oro_datetime',
                array('required' => true, 'label' => 'orocrm.call.call_date_time.label')
            )
            ->add(
                'callStatus',
                'entity',
                array(
                    'label' => 'orocrm.call.call_status.label',
                    'class' => 'OroCRM\Bundle\CallBundle\Entity\CallStatus',
                    'required' => true
                )
            )
            ->add(
                'duration',
                'oro_time_interval',
                array('required' => false, 'label' => 'orocrm.call.duration.label')
            )
            ->add(
                'direction',
                'entity',
                array(
                    'label'    => 'orocrm.call.direction.label',
                    'class'    => 'OroCRM\Bundle\CallBundle\Entity\CallDirection',
                    'required' => true
                )
            );
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'OroCRM\Bundle\CallBundle\Entity\Call',
                'suggestions' => [],
                'default_choice' => null,
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
