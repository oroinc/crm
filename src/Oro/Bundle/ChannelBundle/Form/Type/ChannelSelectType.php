<?php

namespace Oro\Bundle\ChannelBundle\Form\Type;

use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Provider\ChannelsByEntitiesProvider;
use Oro\Bundle\FormBundle\Form\Type\Select2EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for selecting a Channel entity.
 * Provides a custom form field type that extends Select2EntityType for selecting channels,
 * with specific configurations and normalization based on provided entities.
 *
 * @see Resourses\Doc\ChannelSelectType.md
 */
class ChannelSelectType extends AbstractType
{
    public const NAME = 'oro_channel_select_type';

    /**
     * @var ChannelsByEntitiesProvider
     */
    protected $channelsProvider;

    public function __construct(ChannelsByEntitiesProvider $channelsProvider)
    {
        $this->channelsProvider = $channelsProvider;
    }

    public function getName()
    {
        return $this->getBlockPrefix();
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return self::NAME;
    }

    #[\Override]
    public function getParent(): ?string
    {
        return Select2EntityType::class;
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'label'                => 'oro.channel.entity_label',
                'class'                => Channel::class,
                'choice_label'         => 'name',
                'random_id'            => true,
                'choices'              => [],
                'configs'              => [
                    'allowClear'  => true,
                    'placeholder' => 'oro.channel.form.select_channel_type.label'
                ],
                'entities'             => [],
                'translatable_options' => false
            ]
        );

        $resolver->setNormalizer(
            'choices',
            function (Options $options, $value) {
                $entities = $options['entities'];

                return $this->channelsProvider->getChannelsByEntities($entities);
            }
        );
    }
}
