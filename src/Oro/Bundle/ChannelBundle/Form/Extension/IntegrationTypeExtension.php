<?php

namespace Oro\Bundle\ChannelBundle\Form\Extension;

use Oro\Bundle\FormBundle\Utils\FormUtils;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\ChannelBundle\Provider\SettingsProvider;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class IntegrationTypeExtension extends AbstractTypeExtension
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
        if (!$options['disable_customer_datasource_types']) {
            return;
        }

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) {
                /** @var Integration $data */
                $data = $event->getData();
                $form = $event->getForm();

                if ($data === null) {
                    return;
                }

                // Remove integration types that could be created only in scope of the channel
                // if type is already set, then keep choice list as is
                $sourceTypes = $this->settingsProvider->getSourceIntegrationTypes();
                if (!in_array($data->getType(), $sourceTypes)) {
                    $field  = $form->get('type');
                    $config = $field->getConfig()->getOptions();

                    FormUtils::replaceField(
                        $form,
                        'type',
                        ['choices' => array_diff($config['choices'], $sourceTypes)],
                        ['choice_list']
                    );
                }
            },
            100
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(['disable_customer_datasource_types' => true]);
    }
}
