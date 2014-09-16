<?php

namespace OroCRM\Bundle\CampaignBundle\Form\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
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
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @param EmailTransportProvider $emailTransportProvider
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(EmailTransportProvider $emailTransportProvider, DoctrineHelper $doctrineHelper)
    {
        $this->emailTransportProvider = $emailTransportProvider;
        $this->doctrineHelper = $doctrineHelper;
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

        $selectedTransport = $this->getSelectedTransport($data->getTransport());
        if ($selectedTransport) {
            $this->addTransportSettingsForm($selectedTransport, $event->getForm());
            $data->setTransport($selectedTransport->getName());
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
        $formData = $form->getData();

        $transportName = isset($data['transport']) ? $data['transport'] : '';

        $selectedTransport = $this->getSelectedTransport($transportName);
        if ($selectedTransport->getName() != $formData->getTransport()) {
            $newSettings = $this->doctrineHelper
                ->createEntityInstance($selectedTransport->getSettingsEntityFQCN());
            $formData->setTransportSettings($newSettings);
        }

        if ($selectedTransport) {
            $this->addTransportSettingsForm($selectedTransport, $form);
            $formData->setTransport($selectedTransport->getName());
            $form->get('transport')->setData($selectedTransport->getName());
        }

        if ($form->has('transportSettings')) {
            $parentData = $data;
            unset($parentData['transportSettings']);
            $data['transportSettings']['parentData'] = $parentData;
        }

        $event->setData($data);
    }

    /**
     * @param TransportInterface $selectedTransport
     * @param FormInterface $form
     */
    protected function addTransportSettingsForm(TransportInterface $selectedTransport, FormInterface $form)
    {
        if ($selectedTransport) {
            $transportSettingsFormType = $selectedTransport->getSettingsFormType();

            if ($transportSettingsFormType) {
                $form->add('transportSettings', $transportSettingsFormType, ['required' => true]);
            } elseif ($form->has('transportSettings')) {
                $form->remove('transportSettings');
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
