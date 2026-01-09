<?php

namespace Oro\Bundle\AnalyticsBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Defines the form type for RFM category configuration.
 */
class RFMCategoryType extends AbstractType
{
    public const NAME = 'oro_analytics_rfm_category';

    #[\Override]
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => 'Oro\Bundle\AnalyticsBundle\Entity\RFMMetricCategory'
            ]
        );
    }

    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('category_index', HiddenType::class)
            ->add('min_value', HiddenType::class)
            ->add('max_value', HiddenType::class);
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
