<?php
namespace OroCRM\Bundle\ContactBundle\Form\EventListener;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Doctrine\Common\Persistence\ObjectManager;

use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\ContactBundle\Entity\Repository\ContactPhoneRepository;

class ContactPhoneSubscriber implements EventSubscriberInterface
{
    private $om;

    /**
     * Form factory.
     *
     * @var FormFactoryInterface
     */
    private $factory;

    /**
     * Constructor.
     *
     * @param ObjectManager $om
     * @param FormFactoryInterface $factory
     */
    public function __construct(ObjectManager $om, FormFactoryInterface $factory)
    {
        $this->om = $om;
        $this->factory = $factory;
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
     * Removes or adds a contactPhoneField field based on the contact set.
     *
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        $contact = $event->getData();
        $form = $event->getForm();

        if (null === $contact) {
            return;
        }

        /** @var $contact Contact */
        $phones = $contact->getPhones();

        if (null === $phones) {
            return;
        }

        if (count($contact->getPhones())) {
            if ($form->has('contactPhoneNumber')) {
                $config = $form->get('contactPhoneNumber')->getConfig()->getOptions();
                unset($config['choice_list']);
                unset($config['choices']);
            } else {
                $config = array();
            }

            $config['contact'] = $contact;
            $config['query_builder'] = $this->getPhoneClosure($contact);

            if (array_key_exists('auto_initialize', $config)) {
                $config['auto_initialize'] = false;
            }

            $form->add(
                $this->factory->createNamed(
                    'contactPhoneNumber',
                    'orocrm_contact_phone',
                    $contact->getPrimaryPhone(),
                    $config
                )
            );
        }
    }

    /**
     * Removes or adds a contactPhoneField field based on the contact set on submitted form.
     *
     * @param FormEvent $event
     */
    public function preSubmit(FormEvent $event)
    {
        $data = $event->getData();

        /** @var $contact Contact */
        $contact = $this->om->getRepository('OroCRMContactBundle:Contact')
            ->find(isset($data['contact']) ? $data['contact'] : false);

        if ($contact && count($contact->getPhones())) {
            $form = $event->getForm();

            $config = $form->get('contactPhoneNumber')->getConfig()->getOptions();
            unset($config['choice_list']);
            unset($config['choices']);

            $config['contact'] = $contact;
            $config['query_builder'] = $this->getPhoneClosure($contact);

            if (array_key_exists('auto_initialize', $config)) {
                $config['auto_initialize'] = false;
            }

            $form->add(
                $this->factory->createNamed(
                    'contactPhoneNumber',
                    'orocrm_contact_phone',
                    null,
                    $config
                )
            );

            unset($data['phoneNumber']);
        } else {
            unset($data['contactPhoneNumber']);
        }

        $event->setData($data);
    }

    /**
     * @param Contact $contact
     * @return callable
     */
    protected function getPhoneClosure(Contact $contact)
    {
        return function (ContactPhoneRepository $repository) use ($contact) {
            return $repository->getContactPhoneQueryBuilder($contact);
        };
    }
}
