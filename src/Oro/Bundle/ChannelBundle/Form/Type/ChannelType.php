<?php

namespace Oro\Bundle\ChannelBundle\Form\Type;

use Symfony\Component\Form\FormView;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\FormBundle\Utils\FormUtils;
use Oro\Bundle\ChannelBundle\Entity\Channel;
use Oro\Bundle\ChannelBundle\Provider\SettingsProvider;
use Oro\Bundle\ChannelBundle\Form\EventListener\ChannelTypeSubscriber;

class ChannelType extends AbstractType
{
    const NAME = 'oro_channel_form';

    /** @var SettingsProvider */
    protected $settingsProvider;

    /** @var ChannelTypeSubscriber */
    protected $channelTypeSubscriber;

    /**
     * @param SettingsProvider      $settingsProvider
     * @param ChannelTypeSubscriber $channelTypeSubscriber
     */
    public function __construct(SettingsProvider $settingsProvider, ChannelTypeSubscriber $channelTypeSubscriber)
    {
        $this->settingsProvider      = $settingsProvider;
        $this->channelTypeSubscriber = $channelTypeSubscriber;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber($this->channelTypeSubscriber);

        $builder->add(
            'name',
            'text',
            [
                'required' => true,
                'label'    => 'oro.channel.name.label'
            ]
        );
        $builder->add('entities', 'oro_channel_entities');
        $builder->add(
            'channelType',
            'genemu_jqueryselect2_choice',
            [
                'choices'  => $this->settingsProvider->getChannelTypeChoiceList(),
                'required' => true,
                'label'    => 'oro.channel.channel_type.label',
                'configs'  => ['placeholder' => 'oro.channel.form.select_channel_type.label'],
                'empty_value'     => '',
            ]
        );
        $builder->add(
            'status',
            HiddenType::class,
            ['data' => Channel::STATUS_ACTIVE]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        if (isset($view->children['owner'], $view->children['owner']->vars['choices'])
            && count($view->children['owner']->vars['choices']) < 2
        ) {
            FormUtils::appendClass($view->children['owner'], 'hide');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'Oro\\Bundle\\ChannelBundle\\Entity\\Channel'
            ]
        );
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
}
