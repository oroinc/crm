<?php

namespace OroCRM\Bundle\ChannelBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\FormBundle\Utils\FormUtils;

use OroCRM\Bundle\ChannelBundle\Form\Type\ChannelSelectType;
use OroCRM\Bundle\ChannelBundle\Provider\ChannelsByEntitiesProvider;

class SingleChannelModeExtension extends AbstractTypeExtension
{
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
            $view->vars['read_only'] = true;
            FormUtils::appendClass($view, 'hide');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(['single_channel_mode' => true]);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return ChannelSelectType::NAME;
    }
}
