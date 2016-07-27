<?php

namespace OroCRM\Bundle\CallBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CallType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'subject',
                'text',
                [
                    'required' => true,
                    'label'    => 'orocrm.call.subject.label'
                ]
            )
            ->add(
                'phoneNumber',
                'orocrm_call_phone',
                [
                    'required'    => true,
                    'label'       => 'orocrm.call.phone_number.label',
                    'suggestions' => $options['phone_suggestions']
                ]
            )
            ->add(
                'notes',
                'oro_resizeable_rich_text',
                [
                    'required' => false,
                    'label'    => 'orocrm.call.notes.label'
                ]
            )
            ->add(
                'callDateTime',
                'oro_datetime',
                [
                    'required' => true,
                    'label'    => 'orocrm.call.call_date_time.label'
                ]
            )
            ->add(
                'callStatus',
                'translatable_entity',
                [
                    'required' => true,
                    'label'    => 'orocrm.call.call_status.label',
                    'class'    => 'OroCRM\Bundle\CallBundle\Entity\CallStatus'
                ]
            )
            ->add(
                'duration',
                'oro_time_interval',
                [
                    'required' => false,
                    'label'    => 'orocrm.call.duration.label'
                ]
            )
            ->add(
                'direction',
                'translatable_entity',
                [
                    'required' => true,
                    'label'    => 'orocrm.call.direction.label',
                    'class'    => 'OroCRM\Bundle\CallBundle\Entity\CallDirection'
                ]
            );
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class'        => 'OroCRM\Bundle\CallBundle\Entity\Call',
                'phone_suggestions' => []
            ]
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
