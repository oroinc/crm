<?php

namespace OroCRM\Bundle\ChannelBundle\Form\EventListener;

use Oro\Bundle\FormBundle\Utils\FormUtils;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\ChannelBundle\Provider\SettingsProvider;

class ChannelTypeSubscriber implements EventSubscriberInterface
{
    /** @var SettingsProvider */
    protected $settingsProvider;

    /**
     * @param SettingsProvider $settingsProvider
     */
    public function __construct(SettingsProvider $settingsProvider)
    {
        $this->settingsProvider = $settingsProvider;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA => 'preSet',
            FormEvents::POST_SET_DATA => 'postSet',
            FormEvents::PRE_SUBMIT   => 'preSubmit',
        ];
    }

    /**
     * @param FormEvent $event
     */
    public function preSet(FormEvent $event)
    {
        $form = $event->getForm();

        /** @var Channel $data */
        $data = $event->getData();

        if ($data === null) {
            return;
        }

        // builds datasource field
        $datasourceModifier = $this->getDatasourceModifierClosure($data->getChannelType());
        $datasourceModifier($form);

        if (false === $this->settingsProvider->isCustomerIdentityUserDefined($data->getChannelType())) {
            $customerIdentityValue = $this->settingsProvider->getCustomerIdentityFromConfig($data->getChannelType());
            $customerIdentityClosure = $this->getCustomerIdentityClosure($customerIdentityValue);
            $customerIdentityClosure($form);
        }
    }

    public function postSet(FormEvent $event)
    {
        $form = $event->getForm();

        /** @var Channel $data */
        $data = $event->getData();

        if ($data === null) {
            return;
        }

        $form;
    }

    /**
     * @param FormEvent $event
     */
    public function preSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        $channelType        = !empty($data['channelType']) ? $data['channelType'] : null;
        $datasourceModifier = $this->getDatasourceModifierClosure($channelType);
        $datasourceModifier($form);
    }

    /**
     * @param string $channelType
     *
     * @return callable
     */
    protected function getDatasourceModifierClosure($channelType)
    {
        $settingsProvider = $this->settingsProvider;

        return function (FormInterface $form) use ($settingsProvider, $channelType) {
            if ($channelType) {
                $integrationType = $settingsProvider->getIntegrationType($channelType);
                if (false !== $integrationType) {
                    $form->add(
                        'dataSource',
                        'orocrm_channel_datasource_form',
                        [
                            'label'    => 'orocrm.channel.data_source.label',
                            'type'     => $integrationType,
                            'required' => true,
                        ]
                    );
                }
            }
        };
    }

    /**
     * @param string $data
     *
     * @return callable
     */
    protected function getCustomerIdentityClosure($data)
    {
        return function (FormInterface $form) use ($data) {
            FormUtils::replaceField($form, 'customerIdentity', ['data' => $data, 'disabled' => true]);
        };
    }
}
