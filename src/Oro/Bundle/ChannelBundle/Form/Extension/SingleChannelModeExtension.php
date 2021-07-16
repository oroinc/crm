<?php

namespace Oro\Bundle\ChannelBundle\Form\Extension;

use Oro\Bundle\ChannelBundle\Form\Type\ChannelSelectType;
use Oro\Bundle\ChannelBundle\Provider\ChannelsByEntitiesProvider;
use Oro\Bundle\FormBundle\Utils\FormUtils;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SingleChannelModeExtension extends AbstractTypeExtension
{
    /**
     * @var ChannelsByEntitiesProvider
     */
    protected $channelsProvider;

    public function __construct(ChannelsByEntitiesProvider $channelsProvider)
    {
        $this->channelsProvider = $channelsProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (!$options['single_channel_mode']) {
            return;
        }
        $entities = $options['entities'];
        $channels = $this->channelsProvider->getChannelsByEntities($entities);

        if (count($channels) === 1) {
            $channel = reset($channels);
            $builder->addEventListener(
                FormEvents::PRE_SET_DATA,
                function (FormEvent $event) use ($channel) {
                    $event->setData($channel);
                }
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if (!$options['single_channel_mode']) {
            return;
        }
        $entities = $options['entities'];
        $channels = $this->channelsProvider->getChannelsByEntities($entities);
        if (count($channels) === 1) {
            $view->vars['attr']['readonly'] = true;
            FormUtils::appendClass($view, 'hide');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['single_channel_mode' => true]);
    }

    /**
     * {@inheritdoc}
     */
    public static function getExtendedTypes(): iterable
    {
        return [ChannelSelectType::class];
    }
}
