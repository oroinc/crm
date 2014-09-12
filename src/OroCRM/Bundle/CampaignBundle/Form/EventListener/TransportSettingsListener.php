<?php

namespace OroCRM\Bundle\CampaignBundle\Form\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

use OroCRM\Bundle\CampaignBundle\Entity\EmailCampaign;
use OroCRM\Bundle\CampaignBundle\Provider\EmailTransportProvider;
use OroCRM\Bundle\CampaignBundle\Transport\TransportInterface;

class TransportSettingsListener implements EventSubscriberInterface
{
    /**
     * @var EmailTransportProvider
     */
    protected $emailTransportProvider;

    /**
     * @param EmailTransportProvider $emailTransportProvider
     */
    public function __construct(EmailTransportProvider $emailTransportProvider)
    {
        $this->emailTransportProvider = $emailTransportProvider;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA  => 'preSet',
            FormEvents::POST_SET_DATA => 'postSet',
            FormEvents::PRE_SUBMIT    => 'preSubmit'
        ];
    }

    /**
     * Add Transport Settings form if any for existing entities.
     *
     * @param FormEvent $event
     */
    public function preSet(FormEvent $event)
    {
        /** @var EmailCampaign $data */
        $data = $event->getData();
        if ($data === null) {
            return;
        }

        if ($transport = $this->addTransportSettingsForm($data->getTransport(), $event->getForm())) {
            $data->setTransport($transport->getName());
        }
        $event->setData($data);
    }

    /**
     * Set correct transport setting value.
     *
     * @param FormEvent $event
     */
    public function postSet(FormEvent $event)
    {
        /** @var EmailCampaign $data */
        $data = $event->getData();

        if ($data === null) {
            return;
        }

        $form = $event->getForm();
        $form->get('transport')->setData($data->getTransport());
    }

    /**
     * Change transport settings subform to form matching transport passed in request.
     * Pass top level data to transportSettings.
     *
     * @param FormEvent $event
     */
    public function preSubmit(FormEvent $event)
    {
        $data = $event->getData();
        $form = $event->getForm();

        $transportName = isset($data['transport']) ? $data['transport'] : '';
        if ($transport = $this->addTransportSettingsForm($transportName, $event->getForm())) {
            $form->getData()->setTransport($transport->getName());
            $form->get('transport')->setData($transport->getName());
        }

        if ($event->getForm()->has('transportSettings')) {
            $parentData = $event->getData();
            unset($parentData['transportSettings']);
            $data['transportSettings']['parentData'] = $parentData;
        }

        $event->setData($data);
    }

    /**
     * @param string $selectedTransportName
     * @param FormInterface $form
     * @return null|TransportInterface
     */
    protected function addTransportSettingsForm($selectedTransportName, FormInterface $form)
    {
        $selectedTransport = $this->getSelectedTransport($selectedTransportName);
        if ($selectedTransport) {
            $transportSettingsFormType = $selectedTransport->getSettingsFormType();

            if ($transportSettingsFormType) {
                $form->add('transportSettings', $transportSettingsFormType, ['required' => true]);
            } elseif ($form->has('transportSettings')) {
                $form->remove('transportSettings');
            }

            return $selectedTransport;
        }

        return null;
    }

    /**
     * @param string $selectedTransportName
     * @return TransportInterface
     */
    protected function getSelectedTransport($selectedTransportName)
    {
        if ($selectedTransportName) {
            $selectedTransport = $this->emailTransportProvider->getTransportByName($selectedTransportName);
        } else {
            $transportChoices = $this->emailTransportProvider->getTransports();
            $selectedTransport = reset($transportChoices);
        }

        return $selectedTransport;
    }
}
