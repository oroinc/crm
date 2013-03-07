<?php

namespace Oro\Bundle\GridBundle\Filter\ORM;

use Symfony\Component\Translation\TranslatorInterface;
use Sonata\DoctrineORMAdminBundle\Filter\DateFilter as SonataDateFilter;
use Sonata\AdminBundle\Form\Type\Filter\DateType;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;

class DateFilter extends SonataDateFilter implements FilterInterface
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
        $renderSettings[0] = 'oro_grid_type_filter_date';
        return $renderSettings;
    }

    /**
     * @return array
     */
    public function getTypeOptions()
    {
        return array(
            DateType::TYPE_EQUAL
                => $this->translator->trans('label_date_type_equal', array(), 'SonataAdminBundle'),
            DateType::TYPE_GREATER_EQUAL
                => $this->translator->trans('label_date_type_greater_equal', array(), 'SonataAdminBundle'),
            DateType::TYPE_GREATER_THAN
                => $this->translator->trans('label_date_type_greater_than', array(), 'SonataAdminBundle'),
            DateType::TYPE_LESS_EQUAL
                => $this->translator->trans('label_date_type_less_equal', array(), 'SonataAdminBundle'),
            DateType::TYPE_LESS_THAN
                => $this->translator->trans('label_date_type_less_than', array(), 'SonataAdminBundle'),
            DateType::TYPE_NULL
                => $this->translator->trans('label_date_type_null', array(), 'SonataAdminBundle'),
            DateType::TYPE_NOT_NULL
                => $this->translator->trans('label_date_type_not_null', array(), 'SonataAdminBundle'),
        );
    }

    /**
     * @param ProxyQueryInterface $queryBuilder
     * @param array $value
     * @return array
     */
    protected function association(ProxyQueryInterface $queryBuilder, $value)
    {
        $alias = $this->getOption('entity_alias')
            ?: $queryBuilder->entityJoin($this->getParentAssociationMappings());

        return array($alias, $this->getFieldName());
    }
}
