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

        if (null !== $data) {
            $contact = $data->getRelatedContact();
            if (null !== $contact) {
                $formOptions = array(
                    'class' => 'OroCRMContactBundle:ContactPhone',
                    'property_path' => 'contactPhoneNumber',
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
                        'query_builder' => function(ContactPhoneRepository $er) use ($contact) {
                                return $er->getContactPhoneQueryBuilder($contact);
                            },
                        );
            $form->add('contactPhoneNumber', 'entity', $options);
            $form->add('phoneNumber', 'hidden');
        } else {
            $form->add('contactPhoneNumber', 'hidden');
            $form->add('phoneNumber', 'text');
        }
        $event->setData($data);   
    }
}
