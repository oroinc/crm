<?php

namespace OroCRM\Bundle\ChannelBundle\Form\Type;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\EntityBundle\Form\Type\EntityChoiceType;
use Oro\Bundle\FormBundle\Form\Type\ChoiceListItem;
use Oro\Bundle\EntityBundle\Provider\EntityProvider;

use OroCRM\Bundle\ChannelBundle\Provider\SettingsProvider;

class ChannelEntityChoiceType extends EntityChoiceType
{
    const NAME = 'orocrm_channel_entity_choice_form';

    /** @var SettingsProvider */
    protected $settingsProvider;

    /**
     * @param EntityProvider   $provider
     * @param SettingsProvider $settingsProvider
     */
    public function __construct(EntityProvider $provider, SettingsProvider $settingsProvider)
    {
        parent::__construct($provider);

        $this->settingsProvider = $settingsProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $that    = $this;
        $choices = function (Options $options) use ($that) {
            return $that->getChoices($options['show_plural']);
        };

        $defaultConfigs = array(
            'is_translated_option'    => true,
            'placeholder'             => 'oro.entity.form.choose_entity',
            'result_template_twig'    => 'OroEntityBundle:Choice:entity/result.html.twig',
            'selection_template_twig' => 'OroEntityBundle:Choice:entity/selection.html.twig',
        );

        // this normalizer allows to add/override config options outside.
        $configsNormalizer = function (Options $options, $configs) use (&$defaultConfigs, $that) {
            return array_merge($defaultConfigs, $configs);
        };

        $resolver->setDefaults(
            array(
                'choices'     => $choices,
                'empty_value' => '',
                'show_plural' => false,
                'configs'     => $defaultConfigs
            )
        );
        $resolver->setNormalizers(
            array(
                'configs' => $configsNormalizer
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getChoices($showPlural)
    {
        $choices      = [];
        $entitiesList = $this->settingsProvider->getChannelEntitiesChoiceList();

        foreach ($entitiesList as $entityList) {
            $entity     = $this->provider->getEntity($entityList);
            $attributes = [];
            foreach ($entity as $key => $val) {
                if (!in_array($key, ['name'])) {
                    $attributes['data-' . $key] = $val;
                }
            }
            $choices[$entity['name']] = new ChoiceListItem(
                $showPlural ? $entity['plural_label'] : $entity['label'],
                $attributes
            );
        }

        return $choices;
    }
}
