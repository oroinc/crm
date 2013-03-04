<?php

namespace Oro\Bundle\GridBundle\Filter\ORM;

use Symfony\Component\Translation\TranslatorInterface;
use Sonata\DoctrineORMAdminBundle\Filter\NumberFilter as SonataNumberFilter;
use Sonata\AdminBundle\Form\Type\Filter\NumberType;
use Oro\Bundle\GridBundle\Filter\FilterInterface;

class NumberFilter extends SonataNumberFilter implements FilterInterface
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
        $renderSettings[0] = 'oro_grid_type_filter_number';
        return $renderSettings;
    }

    /**
     * @return array
     */
    public function getTypeOptions()
    {
        return array(
            NumberType::TYPE_EQUAL
                => $this->translator->trans('label_type_equal', array(), 'SonataAdminBundle'),
            NumberType::TYPE_GREATER_EQUAL
                => $this->translator->trans('label_type_greater_equal', array(), 'SonataAdminBundle'),
            NumberType::TYPE_GREATER_THAN
                => $this->translator->trans('label_type_greater_than', array(), 'SonataAdminBundle'),
            NumberType::TYPE_LESS_EQUAL
                => $this->translator->trans('label_type_less_equal', array(), 'SonataAdminBundle'),
            NumberType::TYPE_LESS_THAN
                => $this->translator->trans('label_type_less_than', array(), 'SonataAdminBundle'),
        );
    }
}
