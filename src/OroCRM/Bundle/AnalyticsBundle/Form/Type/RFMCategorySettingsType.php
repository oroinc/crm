<?php

namespace OroCRM\Bundle\AnalyticsBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RFMCategorySettingsType extends AbstractType
{
    const NAME = 'orocrm_analytics_rfm_category_settings';
    const TYPE_OPTION = 'rfm_type';

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired([self::TYPE_OPTION]);
        $resolver->setDefaults(
            [
                'type' => RFMCategoryType::NAME,
                'is_increasing' => true,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['attr']['data-increasing'] = (int)$options['is_increasing'];
        $view->vars['attr']['class'] = 'rfm-' . $options[self::TYPE_OPTION];
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'collection';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return self::NAME;
    }
}
