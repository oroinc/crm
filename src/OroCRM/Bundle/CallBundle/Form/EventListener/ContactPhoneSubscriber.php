<?php
namespace OroCRM\Bundle\CallBundle\Form\EventListener;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Doctrine\Common\Persistence\ObjectManager;

use OroCRM\Bundle\ContactBundle\Entity\Repository\ContactPhoneRepository;

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

        $formOptions = $this->getDefaultOptions();

        if (null !== $data) {
            $contact = $data->getRelatedContact();
            if (null !== $contact) {
                $formOptions['query_builder'] = function (ContactPhoneRepository $er) use ($contact) {
                    return $er->getContactPhoneQueryBuilder($contact);
                };
            } else {
                $formOptions['choices'] = array();
            }
        }

        $form->add('contactPhoneNumber', 'entity', $formOptions);
    }

    /**
     * @param FormEvent $event
     */
    public function preSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        $formOptions = $this->getDefaultOptions();

        if (!empty($data['relatedContact'])) {
            $contact = $this->om
                            ->getRepository('OroCRMContactBundle:Contact')
                            ->find($data['relatedContact']);

            $formOptions['query_builder'] = function (ContactPhoneRepository $er) use ($contact) {
                    return $er->getContactPhoneQueryBuilder($contact);
            };

        } else {
            $formOptions['choices'] = array();
        }

        $form->add('contactPhoneNumber', 'entity', $formOptions);

        $event->setData($data);
    }

    protected function getDefaultOptions()
    {
        return array(
            'class' => 'OroCRMContactBundle:ContactPhone',
            'property' => 'phone',
            'empty_value' => 'orocrm.call.form.call.other',
            'label' => 'orocrm.call.contact_phone_number.label',
            'required' => true);
    }
}
