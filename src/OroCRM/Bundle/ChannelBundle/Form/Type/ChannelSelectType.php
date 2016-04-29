<?php

namespace OroCRM\Bundle\ChannelBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\Options;

use OroCRM\Bundle\ChannelBundle\Provider\ChannelsByEntitiesProvider;

/**
 * Class ChannelSelectType
 *
 * @see Resourses\Doc\ChannelSelectType.md
 */
class ChannelSelectType extends AbstractType
{
    const NAME = 'orocrm_channel_select_type';

    /**
     * @var ChannelsByEntitiesProvider
     */
    protected $channelsProvider;

    /**
     * @param ChannelsByEntitiesProvider $channelsProvider
     */
    public function __construct(ChannelsByEntitiesProvider $channelsProvider)
    {
        $this->channelsProvider = $channelsProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'genemu_jqueryselect2_entity';
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'label'                => 'orocrm.channel.entity_label',
                'class'                => 'OroCRMChannelBundle:Channel',
                'property'             => 'name',
                'random_id'            => true,
                'choices'              => [],
                'configs'              => [
                    'allowClear'  => true,
                    'placeholder' => 'orocrm.channel.form.select_channel_type.label'
                ],
                'entities'             => [],
                'translatable_options' => false
            ]
        );

        $resolver->setNormalizers(
            [
                'choices' => function (Options $options, $value) {
                    $entities = $options['entities'];

                    return $this->channelsProvider->getChannelsByEntities($entities);
                }
            ]
        );
    }
}
