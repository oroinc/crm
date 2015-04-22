<?php

namespace OroCRM\Bundle\ChannelBundle\Form\Type;

use Symfony\Component\Form\FormView;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\FormBundle\Utils\FormUtils;

use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\ChannelBundle\Provider\SettingsProvider;
use OroCRM\Bundle\ChannelBundle\Form\EventListener\ChannelTypeSubscriber;

class ChannelType extends AbstractType
{
    const NAME = 'orocrm_channel_form';

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
                'label'    => 'orocrm.channel.name.label'
            ]
        );
        $builder->add('entities', 'orocrm_channel_entities');
        $builder->add(
            'channelType',
            'genemu_jqueryselect2_choice',
            [
                'choices'  => $this->settingsProvider->getChannelTypeChoiceList(),
                'required' => true,
                'label'    => 'orocrm.channel.channel_type.label',
                'configs'  => ['placeholder' => 'orocrm.channel.form.select_channel_type.label'],
                'empty_value'     => '',
            ]
        );
        $builder->add(
            'status',
            'choice',
            [
                'choices'  => [
                    Channel::STATUS_INACTIVE    => 'orocrm.channel.inactive.label',
                    Channel::STATUS_ACTIVE      => 'orocrm.channel.active.label'
                ],
                'required' => true,
                'label'    => 'orocrm.channel.status.label',
            ]
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
                'data_class' => 'OroCRM\\Bundle\\ChannelBundle\\Entity\\Channel'
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
