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
            FormEvents::PRE_SET_DATA => 'preSet',
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

        // rebuilds customer identity field
        $selectedEntities = $data->getEntities();
        $entityChoices    = $form->get('entities')->getConfig()->getOption('choices');
        $choices          = array_intersect_key($entityChoices, array_flip($selectedEntities));

        $customerIdentityModifier = $this->getCustomerIdentityModifierClosure($choices);
        $customerIdentityModifier($form);

        // builds datasource field
        $datasourceModifier = $this->getDatasourceModifierClosure($data->getChannelType());
        $datasourceModifier($form);
    }

    /**
     * @param FormEvent $event
     */
    public function preSubmit(FormEvent $event)
    {
        $form     = $event->getForm();
        $data     = $event->getData();
        $entities = !empty($data['entities']) ? $data['entities'] : [];

        if (!empty($data['customerIdentity'])) {
            if (in_array($data['customerIdentity'], $entities)) {
                $choices                  = array_flip([$data['customerIdentity']]);
                $customerIdentityModifier = $this->getCustomerIdentityModifierClosure($choices);
                $customerIdentityModifier($form);
            }
        }

        // builds datasource field
        $channelType        = !empty($data['channelType']) ? $data['channelType'] : null;
        $datasourceModifier = $this->getDatasourceModifierClosure($channelType);
        $datasourceModifier($form);
    }

    /**
     * @param array $choices
     *
     * @return callable
     */
    protected function getCustomerIdentityModifierClosure(array $choices)
    {
        return function (FormInterface $form) use ($choices) {
            if (!$choices) {
                return;
            }
            FormUtils::replaceField($form, 'customerIdentity', ['choices' => $choices], ['choice_list']);
        };
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
}
