<?php
namespace OroCRM\Bundle\CallBundle\Form\EventListener;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Doctrine\Common\Persistence\ObjectManager;

use OroCRM\Bundle\ContactBundle\Entity\Repository\ContactPhoneRepository;
use OroCRM\Bundle\ContactBundle\Entity\Contact;

class ContactPhoneSubscriber implements EventSubscriberInterface
{
    /**
    * ObjectManager $om
    */
    private $om;

    /**
     * Constructor.
     *
     * @param ObjectManager $om
     */
    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::PRE_SUBMIT   => 'preSubmit'
        );
    }

    /**
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        $formOptions = array(
            'class' => 'OroCRMContactBundle:ContactPhone',
            'property_path' => 'contactPhoneNumber',
            'property' => 'phone',
            'empty_value' => '...',
            'label' => 'Phone Number',
            'required' => true
            );

        if (null !== $data) {
            $contact = $data->getRelatedContact();
            if (null !== $contact) {
                $formOptions['query_builder'] = function (ContactPhoneRepository $er) use ($contact) {
                            return $er->getContactPhoneQueryBuilder($contact);
                };
            }
        } 
        
        $form->add('phoneNumber', 'text', array('required' => true, 'attr' => array('class' => 'hide')));
        $form->add('contactPhoneNumber', 'entity', $formOptions);
    }

    /**
     * @param FormEvent $event
     */
    public function preSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        if ($data['relatedContact']) {
            $contact = $this->om
                            ->getRepository('OroCRMContactBundle:Contact')
                            ->find($data['relatedContact']);

            $options = $form->get('contactPhoneNumber')->getConfig()->getOptions();
            $options = array(
                        'class' => 'OroCRMContactBundle:ContactPhone',
                        'property' => 'phone',
                        'required' => false,
                        'query_builder' => function (ContactPhoneRepository $er) use ($contact) {
                                return $er->getContactPhoneQueryBuilder($contact);
                        },
                        );
            $form->add('contactPhoneNumber', 'entity', $options);
            $form->add('phoneNumber', 'text', array('required' => false));
        } else {
            $form->add('contactPhoneNumber', 'hidden');
            $form->add('phoneNumber', 'text', array('required' => false));
        }
        $event->setData($data);
    }
}
