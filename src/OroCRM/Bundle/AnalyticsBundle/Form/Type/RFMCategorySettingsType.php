<?php

namespace OroCRM\Bundle\AnalyticsBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RFMCategorySettingsType extends AbstractType
{
    const NAME = 'orocrm_analytics_rfm_category_settings';

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(['rfm_type']);
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
        $view->vars['is_increasing'] = (bool)$options['is_increasing'];
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
