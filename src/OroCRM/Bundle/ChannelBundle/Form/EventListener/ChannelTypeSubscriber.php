<?php

namespace OroCRM\Bundle\ChannelBundle\Form\EventListener;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Oro\Bundle\FormBundle\Utils\FormUtils;

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
            FormEvents::PRE_SET_DATA  => 'preSet',
            FormEvents::POST_SET_DATA => 'postSet',
            FormEvents::PRE_SUBMIT    => 'preSubmit',
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

        if (!$data->getChannelType()) {
            $data->setChannelType($this->getFirstChannelType());
        }

        // builds datasource field
        $datasourceModifier = $this->getDatasourceModifierClosure($data->getChannelType());
        $datasourceModifier($form);

        $readOnly   = !$this->settingsProvider->isCustomerIdentityUserDefined($data->getChannelType());
        $predefined = $this->settingsProvider->getCustomerIdentityFromConfig($data->getChannelType());

        // pre-fill customer identity for new instances, or if it's not customer defined for this channel type
        if ((!$data->getId() || $readOnly) && null !== $predefined) {
            $data->setCustomerIdentity($predefined);

            // also add to entities
            $entities = $data->getEntities();
            $entities = is_array($entities) ? $entities : [];
            if (!in_array($predefined, $entities, true)) {
                $entities[] = $predefined;
                $data->setEntities($entities);
            }
        }

        // pre-fill entities for new instances
        if (!$data->getId()) {
            $channelTypeEntities = $this->settingsProvider->getEntitiesByChannelType($data->getChannelType());
            $entities            = $data->getEntities();
            $entities            = is_array($entities) ? $entities : [];
            $data->setEntities(array_unique(array_merge($entities, $channelTypeEntities)));
        }

    }

    /**
     * @param FormEvent $event
     */
    public function postSet(FormEvent $event)
    {
        $form = $event->getForm();
        /** @var Channel $data */
        $data = $event->getData();

        if ($data === null) {
            return;
        }

        // disable modification of customer identity and channel type after save
        if ($data->getId()) {
            FormUtils::replaceField(
                $form,
                'customerIdentity',
                ['required' => false, 'disabled' => true]
            );
            FormUtils::replaceField(
                $form,
                'channelType',
                ['required' => false, 'disabled' => true]
            );
        } elseif (!$data->getId() && $data->getChannelType()) {
            // mark customer identity readonly if it's not customer defined for this channel type
            $readOnly = !$this->settingsProvider->isCustomerIdentityUserDefined($data->getChannelType());
            FormUtils::replaceField($form, 'customerIdentity', ['read_only' => $readOnly]);
        }
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
                            'label'          => 'orocrm.channel.data_source.label',
                            'type'           => $integrationType,
                            'required'       => true,
                            'error_bubbling' => false
                        ]
                    );
                }
            }
        };
    }

    /**
     * @return string
     */
    protected function getFirstChannelType()
    {
        $channelTypes = $this->settingsProvider->getChannelTypeChoiceList();
        reset($channelTypes);
        return (string)key($channelTypes);
    }
}
