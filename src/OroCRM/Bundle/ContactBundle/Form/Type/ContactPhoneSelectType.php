<?php

namespace OroCRM\Bundle\ContactBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\AbstractType;

use OroCRM\Bundle\ContactBundle\Form\EventListener\ContactPhoneSubscriber;

class ContactPhoneSelectType extends AbstractType
{
    /**
     * @var ContactPhoneSubscriber
     */
    private $contactPhoneSubscriber;

    /**
     * @param ContactPhoneSubscriber $eventListener
     */
    public function __construct(ContactPhoneSubscriber $eventListener)
    {
        $this->contactPhoneSubscriber = $eventListener;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber($this->contactPhoneSubscriber);

        $builder
            ->add('contact', 'orocrm_contact_select', array('required' => true, 'label' => 'Contact'))
            ->add('contactPhoneNumber', 'orocrm_contact_phone', array('required' => false, 'label' => 'Contact Phone Number'))
            ->add('phoneNumber', 'text', array('required' => false, 'label' => 'Custom Phone Number'));
    }


    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class'           => 'OroCRM\Bundle\ContactBundle\Entity\ContactPhone',
                'extra_fields_message' => 'This form should not contain extra fields: "{{ extra_fields }}"',
                'single_form'          => true
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'orocrm_contact_with_phone_select';
    }
}
