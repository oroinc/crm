<?php

namespace OroCRM\Bundle\ChannelBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use OroCRM\Bundle\ChannelBundle\Provider\SettingsProvider;

class ChannelType extends AbstractType
{
    const NAME = 'orocrm_channel_form';

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
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'name',
            'text',
            [
                'required' => true,
                'label'    => 'orocrm.channel.name.label'
            ]
        );
        $builder->add(
            'description',
            'textarea',
            [
                'required' => true,
                'label'    => 'orocrm.channel.description.label'
            ]
        );
        $builder->add(
            'entities',
            'orocrm_channel_entity_choice_form',
            [
                'required' => false,
                'multiple' => true,
                'label'    => 'orocrm.channel.entities.label',
                'configs'  => ['placeholder' => 'orocrm.channel.form.select_entities.label']
            ]
        );
        $builder->add(
            'integrations',
            'genemu_jqueryselect2_entity',
            [
                'required' => false,
                'multiple' => true,
                'label'    => 'orocrm.channel.integrations.label',
                'class'    => 'Oro\Bundle\IntegrationBundle\Entity\Channel',
                'configs'  => ['placeholder' => 'orocrm.channel.form.select_integrations.label'],
                'property' => 'name',
            ]
        );
        $builder->add(
            'dataSource',
            'oro_integration_select',
            [
                'required'      => false,
                'allowed_types' => $this->getAllowedDataSourceIntegrationTypes(),
                'label'         => 'orocrm.channel.data_source.label',
                'configs'       => ['placeholder' => 'orocrm.channel.form.select_data_source.label'],
            ]
        );
    }

    /**
     * @return array
     */
    protected function getAllowedDataSourceIntegrationTypes()
    {
        $settings     = $this->settingsProvider->getSettings('entity_data');
        $allowedTypes = [];

        foreach (array_keys($settings) as $entityName) {
            if ($this->settingsProvider->belongsToIntegration($entityName)) {
                $allowedTypes[] = $this->settingsProvider->getIntegrationTypeData($entityName);
            }
        }

        return $allowedTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        if (isset($view->children['owner'], $view->children['owner']->vars['choices'])
            && count($view->children['owner']->vars['choices']) === 1
        ) {

            $this->appendClassAttr($view->children['owner']->vars, 'hide');
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

    /**
     * @param array  $options
     * @param string $cssClass
     */
    protected function appendClassAttr(array &$options, $cssClass)
    {
        $options['attr']          = isset($options['attr']) ? $options['attr'] : [];
        $options['attr']['class'] = isset($options['attr']['class']) ? $options['attr']['class'] : '';

        $options['attr']['class'] = implode(' ', [$options['attr']['class'], $cssClass]);
    }
}
