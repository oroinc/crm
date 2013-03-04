<?php

namespace Oro\Bundle\GridBundle\Filter\ORM;

use Symfony\Component\Translation\TranslatorInterface;
use Sonata\DoctrineORMAdminBundle\Filter\ChoiceFilter as SonataChoiceFilter;
use Sonata\AdminBundle\Form\Type\Filter\ChoiceType;
use Oro\Bundle\GridBundle\Filter\FilterInterface;

class ChoiceFilter extends SonataChoiceFilter implements FilterInterface
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function getRenderSettings()
    {
        $renderSettings    = parent::getRenderSettings();
        $renderSettings[0] = 'oro_grid_type_filter_default';
        return $renderSettings;
    }

    /**
     * @return array
     */
    public function getTypeOptions()
    {
        return array(
            ChoiceType::TYPE_CONTAINS
                => $this->translator->trans('label_type_contains', array(), 'SonataAdminBundle'),
            ChoiceType::TYPE_NOT_CONTAINS
                => $this->translator->trans('label_type_not_contains', array(), 'SonataAdminBundle'),
            ChoiceType::TYPE_EQUAL
                => $this->translator->trans('label_type_equals', array(), 'SonataAdminBundle'),
        );
    }
}
