<?php

namespace Oro\Bundle\AnalyticsBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Defines the form type for RFM category settings configuration.
 */
class RFMCategorySettingsType extends AbstractType
{
    public const NAME = 'oro_analytics_rfm_category_settings';
    public const TYPE_OPTION = 'rfm_type';

    #[\Override]
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

    #[\Override]
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['attr']['data-increasing'] = (int)$options['is_increasing'];
        $view->vars['attr']['class'] = 'rfm-' . $options[self::TYPE_OPTION];
    }

    #[\Override]
    public function getParent(): ?string
    {
        return CollectionType::class;
    }

    public function getName()
    {
        return $this->getBlockPrefix();
    }

    #[\Override]
    public function getBlockPrefix(): string
    {
        return self::NAME;
    }
}
