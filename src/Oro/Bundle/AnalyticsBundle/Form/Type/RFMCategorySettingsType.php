<?php

namespace Oro\Bundle\AnalyticsBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RFMCategorySettingsType extends AbstractType
{
    const NAME = 'oro_analytics_rfm_category_settings';
    const TYPE_OPTION = 'rfm_type';

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired([self::TYPE_OPTION]);
        $resolver->setDefaults(
            [
                'entry_type' => RFMCategoryType::class,
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
        return CollectionType::class;
    }

    /**
     * {@inheritdoc}
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
