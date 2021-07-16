<?php

namespace Oro\Bundle\ChannelBundle\Form\EventListener;

use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Form\Type\ChannelDatasourceType;
use Oro\Bundle\ChannelBundle\Provider\SettingsProvider;
use Oro\Bundle\FormBundle\Form\Type\Select2ChoiceType;
use Oro\Bundle\FormBundle\Utils\FormUtils;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;

/**
 * Handles "channelType" field for the channel form.
 */
class ChannelTypeSubscriber implements EventSubscriberInterface
{
    /** @var SettingsProvider */
    private $settingsProvider;

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
            FormEvents::POST_SUBMIT   => ['postSubmit', 20]
        ];
    }

    public function preSet(FormEvent $event)
    {
        /** @var Channel $data */
        $data = $event->getData();
        if ($data === null) {
            return;
        }

        $this->setDefaultValues($data);

        $channelType = $data->getChannelType();
        $form = $event->getForm();

        $this->buildDatasourceField($form, $channelType);

        $customerIdentity = $this->getCustomerIdentity($channelType);
        $data->setCustomerIdentity($customerIdentity);
        if ($customerIdentity) {
            $this->addEntitiesToChannel($data, [$customerIdentity]);
        }

        // pre-fill entities for new instances
        if (!$data->getId()) {
            $this->addEntitiesToChannel($data, $this->getEntities($channelType));

            // restrict to choose non system channels if create action
            $form->remove('channelType');
            $form->add(
                'channelType',
                Select2ChoiceType::class,
                [
                    'choices'     => $this->settingsProvider->getNonSystemChannelTypeChoiceList(),
                    'required'    => true,
                    'label'       => 'oro.channel.channel_type.label',
                    'configs'     => ['placeholder' => 'oro.channel.form.select_channel_type.label'],
                    'placeholder' => ''
                ]
            );
        }
    }

    public function postSet(FormEvent $event)
    {
        /** @var Channel $data */
        $data = $event->getData();
        if ($data === null) {
            return;
        }

        // disable modification of customer identity and channel type after save
        if ($data->getId()) {
            FormUtils::replaceField(
                $event->getForm(),
                'channelType',
                ['required' => false, 'disabled' => true]
            );
        }
    }

    public function preSubmit(FormEvent $event)
    {
        $data = $event->getData();
        $channelType = !empty($data['channelType']) ? $data['channelType'] : null;
        $this->buildDatasourceField($event->getForm(), $channelType);
    }

    public function postSubmit(FormEvent $event)
    {
        /** @var Channel $data */
        $data = $event->getData();
        if (!$data->getId()) {
            $data->setCustomerIdentity($this->getCustomerIdentity($data->getChannelType()));
        }
    }

    /**
     * @param object $object
     */
    private function setDefaultValues($object)
    {
        // set default status to active
        if ($object instanceof Channel && !$object->getChannelType()) {
            $object->setStatus(Channel::STATUS_ACTIVE);
        }
    }

    /**
     * @param FormInterface $form
     * @param string|null   $channelType
     */
    private function buildDatasourceField(FormInterface $form, $channelType)
    {
        if (!$channelType) {
            return;
        }

        $integrationType = $this->settingsProvider->getIntegrationType($channelType);
        if (!$integrationType) {
            return;
        }

        $form->add(
            'dataSource',
            ChannelDatasourceType::class,
            [
                'label'          => 'oro.channel.data_source.label',
                'type'           => $integrationType,
                'required'       => true,
                'error_bubbling' => false
            ]
        );
    }

    /**
     * @param string|null $channelType
     *
     * @return string|null
     */
    private function getCustomerIdentity($channelType)
    {
        return $channelType
            ? $this->settingsProvider->getCustomerIdentityFromConfig($channelType)
            : null;
    }

    /**
     * @param string|null $channelType
     *
     * @return string[]
     */
    private function getEntities($channelType)
    {
        return $channelType
            ? $this->settingsProvider->getEntitiesByChannelType($channelType)
            : [];
    }

    private function addEntitiesToChannel(Channel $channel, array $entitiesToAdd)
    {
        $entities = $channel->getEntities();
        $entities = is_array($entities) ? $entities : [];
        $combinedEntities = array_unique(array_merge($entities, $entitiesToAdd));
        $channel->setEntities($combinedEntities);
    }
}
