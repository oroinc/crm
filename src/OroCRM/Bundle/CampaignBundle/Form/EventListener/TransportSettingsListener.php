<?php

namespace OroCRM\Bundle\CampaignBundle\Form\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

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
            FormEvents::PRE_SUBMIT => 'preSubmit'
        ];
    }

    /**
     * Add Transport Settings form if any
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

        $selectedTransport = $this->getSelectedTransport($data);
        if ($selectedTransport) {
            $transportSettingsFormType = $selectedTransport->getSettingsFormType();

            if ($transportSettingsFormType) {
                $form = $event->getForm();
                $form->add('transportSettings', $transportSettingsFormType, ['required' => true]);
            }

            $data->setTransport($selectedTransport->getName());
        }
    }

    /**
     * Pass top level data to transportSettings.
     *
     * @param FormEvent $event
     */
    public function preSubmit(FormEvent $event)
    {
        if ($event->getForm()->has('transportSettings')) {
            $data = $event->getData();
            $parentData = $event->getData();
            unset($parentData['transportSettings']);
            $data['transportSettings']['parentData'] = $parentData;
            $event->setData($data);
        }
    }

    /**
     * @param EmailCampaign $data
     * @return TransportInterface
     */
    protected function getSelectedTransport(EmailCampaign $data)
    {
        $selectedTransportName = $data->getTransport();
        if ($selectedTransportName) {
            $selectedTransport = $this->emailTransportProvider->getTransportByName($selectedTransportName);
        } else {
            $transportChoices = $this->emailTransportProvider->getTransports();
            $selectedTransport = reset($transportChoices);
        }

        return $selectedTransport;
    }
}
