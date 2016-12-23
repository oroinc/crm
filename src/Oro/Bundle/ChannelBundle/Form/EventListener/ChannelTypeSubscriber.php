<?php

namespace Oro\Bundle\ChannelBundle\Form\EventListener;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Oro\Bundle\FormBundle\Utils\FormUtils;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Provider\SettingsProvider;

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
            FormEvents::POST_SUBMIT   => ['postSubmit', 20],
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

        $this->setDefaultValues($data);

        // builds datasource field
        $datasourceModifier = $this->getDatasourceModifierClosure($data->getChannelType());
        $datasourceModifier($form);

        $customerIdentity = $this->settingsProvider->getCustomerIdentityFromConfig($data->getChannelType());
        $data->setCustomerIdentity($customerIdentity);
        if (!empty($customerIdentity)) {
            $this->addEntitiesToChannel($data, [$customerIdentity]);
        }

        // pre-fill entities for new instances
        if (!$data->getId()) {
            $channelTypeEntities = $this->settingsProvider->getEntitiesByChannelType($data->getChannelType());
            $this->addEntitiesToChannel($data, $channelTypeEntities);

            // restrict to choose non system channels if create action
            $form->remove('channelType');
            $form->add(
                'channelType',
                'genemu_jqueryselect2_choice',
                [
                    'choices'  => $this->settingsProvider->getNonSystemChannelTypeChoiceList(),
                    'required' => true,
                    'label'    => 'oro.channel.channel_type.label',
                    'configs'  => ['placeholder' => 'oro.channel.form.select_channel_type.label'],
                    'empty_value' => '',
                ]
            );
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
                'channelType',
                ['required' => false, 'disabled' => true]
            );
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
     * @param FormEvent $event
     */
    public function postSubmit(FormEvent $event)
    {
        $data             = $event->getData();
        $customerIdentity = $this->settingsProvider->getCustomerIdentityFromConfig($data->getChannelType());

        if (!$data->getId()) {
            $data->setCustomerIdentity($customerIdentity);
        }
    }

    /**
     * @param object $object
     */
    protected function setDefaultValues($object)
    {
        //set default status to active
        if ($object instanceof Channel && !$object->getChannelType()) {
            $object->setStatus(Channel::STATUS_ACTIVE);
        }
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
                        'oro_channel_datasource_form',
                        [
                            'label'          => 'oro.channel.data_source.label',
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

        return (string) key($channelTypes);
    }

    /**
     * @param Channel $channel
     * @param array   $entitiesToAdd
     */
    protected function addEntitiesToChannel(Channel $channel, array $entitiesToAdd)
    {
        $entities         = $channel->getEntities();
        $entities         = is_array($entities) ? $entities : [];
        $combinedEntities = array_unique(array_merge($entities, $entitiesToAdd));
        $channel->setEntities($combinedEntities);
    }
}
