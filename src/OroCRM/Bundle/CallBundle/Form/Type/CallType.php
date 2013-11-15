<?php

namespace OroCRM\Bundle\CallBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

use OroCRM\Bundle\ContactBundle\Entity\Repository\ContactPhoneRepository;

class CallType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener( FormEvents::PRE_SET_DATA,
            function(FormEvent $event) {
                $form = $event->getForm();
                $data = $event->getData();

                if (null !== $data) {
                    $contact = $data->getRelatedContact();
                    if (null !== $contact) {
                        $formOptions = array(
                            'class' => 'OroCRMContactBundle:ContactPhone',
                            'property' => 'phone',
                            'query_builder' => function(ContactPhoneRepository $er) use ($contact) {
                                    return $er->getContactPhoneQueryBuilder($contact);
                                },
                            );
                        $form->add('contactPhoneNumber', 'entity', $formOptions);
                        $form->add('phoneNumber', 'hidden');
                    } 

                } else {
                        $form->add('contactPhoneNumber', 'hidden');
                        $form->add('phoneNumber', 'text');
                }
                
            });

        $builder
            ->add('owner', null, array('required' => true))
            ->add('relatedAccount', 'orocrm_account_select', array('required' => false))
            ->add('subject', 'text', array('required' => true))
            ->add('relatedContact', 'orocrm_contact_select', array('required' => false))
            ->add('contactPhoneNumber', 'orocrm_contactphone', array('required' => false))
            ->add('phoneNumber', 'hidden')
            ->add('notes', 'textarea', array('required' => false))
            ->add('callDateTime', 'oro_datetime', array('required' => true))
            ->add('callStatus', 'hidden', array('property_path' => 'callStatus.id'))
            ->add('duration', 'time', array('required' => false, 'widget' => 'single_text', 'with_seconds' => true))
            ->add('direction', null, array('required'  => true));
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
