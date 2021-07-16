<?php

namespace Oro\Bundle\ChannelBundle\Form\Extension;

use Oro\Bundle\ChannelBundle\Provider\SettingsProvider;
use Oro\Bundle\FormBundle\Utils\FormUtils;
use Oro\Bundle\IntegrationBundle\Entity\Channel as Integration;
use Oro\Bundle\IntegrationBundle\Form\Type\ChannelType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IntegrationTypeExtension extends AbstractTypeExtension
{
    /** @var SettingsProvider */
    protected $settingsProvider;

    public function __construct(SettingsProvider $settingsProvider)
    {
        $this->settingsProvider = $settingsProvider;
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        return [ChannelType::class];
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
                        ['choices' => array_diff($config['choices'], $sourceTypes)]
                    );
                }
            },
            100
        );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['disable_customer_datasource_types' => true]);
    }
}
