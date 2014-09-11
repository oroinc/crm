<?php

namespace OroCRM\Bundle\CampaignBundle\Form\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use OroCRM\Bundle\CampaignBundle\Entity\EmailCampaign;
use OroCRM\Bundle\CampaignBundle\Provider\EmailTransportProvider;
use OroCRM\Bundle\CampaignBundle\Transport\TransportInterface;
use Symfony\Component\Form\FormInterface;

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

        $this->addTransportSettingsForm($data->getTransport(), $event->getForm());
    }

    /**
     * Pass top level data to transportSettings.
     *
     * @param FormEvent $event
     */
    public function preSubmit(FormEvent $event)
    {
        $data = $event->getData();

        $transportName = isset($data['transport']) ? $data['transport'] : '';
        $this->addTransportSettingsForm($transportName, $event->getForm());

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
     */
    protected function addTransportSettingsForm($selectedTransportName, FormInterface $form)
    {
        $selectedTransport = $this->getSelectedTransport($selectedTransportName);
        if ($selectedTransport) {
            $transportSettingsFormType = $selectedTransport->getSettingsFormType();

            if ($transportSettingsFormType) {
                $form->add('transportSettings', $transportSettingsFormType, ['required' => true]);
            }
        }
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
