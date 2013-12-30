<?php

namespace OroCRM\Bundle\CallBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use OroCRM\Bundle\CallBundle\Form\EventListener\ContactPhoneSubscriber;

class CallType extends AbstractType
{
    private $contactPhoneSubscriber;

    /**
     * Constructor.
     *
     * @param ContactPhoneSubscriber $contactPhoneSubscriber
     */
    public function __construct(ContactPhoneSubscriber $contactPhoneSubscriber)
    {
        $this->contactPhoneSubscriber = $contactPhoneSubscriber;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber($this->contactPhoneSubscriber);

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
                'contactPhoneNumber',
                'entity',
                array(
                    'label'    => 'orocrm.call.contact_phone_number.label',
                    'class'    => 'OroCRM\Bundle\ContactBundle\Entity\ContactPhone',
                    'required' => false
                )
            )
            ->add(
                'phoneNumber',
                'text',
                array(
                    'label'    => 'orocrm.call.phone_number.label',
                    'required' => false,
                    'attr'     => array('class' => 'hide')
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
                'error_mapping' => array(
                    '.' => 'contactPhoneNumber',
                ),
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
