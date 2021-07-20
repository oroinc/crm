<?php

namespace Oro\Bundle\ChannelBundle\Form\Type;

use Oro\Bundle\ChannelBundle\Provider\ChannelsByEntitiesProvider;
use Oro\Bundle\FormBundle\Form\Type\Select2EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ChannelSelectType
 *
 * @see Resourses\Doc\ChannelSelectType.md
 */
class ChannelSelectType extends AbstractType
{
    const NAME = 'oro_channel_select_type';

    /**
     * @var ChannelsByEntitiesProvider
     */
    protected $channelsProvider;

    public function __construct(ChannelsByEntitiesProvider $channelsProvider)
    {
        $this->channelsProvider = $channelsProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return Select2EntityType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'label'                => 'oro.channel.entity_label',
                'class'                => 'OroChannelBundle:Channel',
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
