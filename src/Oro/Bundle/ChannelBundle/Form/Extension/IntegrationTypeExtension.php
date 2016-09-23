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
                    foreach ($sourceTypes as $sourceType) {
                        unset($config['choices'][$sourceType]);
                    }

                    $unsetOptions = ['choice_list'];

                    /**
                     * @todo: should be removed in scope of BAP-11222
                     */
                    /* Check if right now we're using Symfony 2.8+ */
                    if (method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix') &&
                        is_callable($config['choice_label'])) {
                        /* It's undocumented features of Symfony 2.8 that will be removed in Symfony 3.0+  */
                        foreach ($config['choices'] as $choice => $labelId) {
                            $config['choices'][$choice] = call_user_func($config['choice_label'], null, $labelId);
                        }
                        array_push($unsetOptions, 'choice_label');
                    }

                    FormUtils::replaceField(
                        $form,
                        'type',
                        ['choices' => $config['choices']],
                        $unsetOptions
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
