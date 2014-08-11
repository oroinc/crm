<?php

namespace OroCRM\Bundle\ChannelBundle\Form\Extension;

use Oro\Bundle\FormBundle\Utils\FormUtils;

use OroCRM\Bundle\ChannelBundle\Provider\SettingsProvider;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class IntegrationTypeExtension extends AbstractTypeExtension
{
    /** @var \OroCRM\Bundle\ChannelBundle\Provider\SettingsProvider */
    protected $settingsProvider;

    /**
     * @param SettingsProvider $settingsProvider
     */
    public function __construct(SettingsProvider $settingsProvider)
    {
        $this->settingsProvider = $settingsProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return 'oro_integration_channel_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
                $form = $event->getForm();
                $data = $event->getData();

                if ($data === null) {
                    return;
                }

                if (!($data && $data->getId())) {
                    $field = $form->get('type');
                    $config = $field->getConfig()->getOptions();
                    $sourceTypes = $this->settingsProvider->getSourceIntegrationTypes();
                    foreach ($sourceTypes as $sourceType) {
                        unset($config['choices'][$sourceType]);
                    }
                    FormUtils::replaceField(
                        $form,
                        'type',
                        ['choices' => $config['choices']],
                        ['choice_list']
                    );
                }
            },
            100
        );
    }
}
