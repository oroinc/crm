<?php

namespace Oro\Bundle\GridBundle\Filter\ORM\Flexible;

use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\Type\Filter\NumberType;
use Oro\Bundle\GridBundle\Filter\ORM\AbstractDateFilter;
use Sonata\AdminBundle\Form\Type\Filter\DateRangeType;

abstract class AbstractFlexibleDateFilter extends AbstractFlexibleFilter
{
    /**
     * @var AbstractDateFilter
     */
    protected $parentFilter;

    /**
     * {@inheritdoc}
     */
    public function filter(ProxyQueryInterface $queryBuilder, $alias, $field, $data)
    {
        if (!$this->parentFilter->isParametersCorrect($data)) {
            return;
        }

        $parameters     = $this->parentFilter->getFilterParameters($data);
        $dateStartValue = $parameters['date_start'];
        $dateEndValue   = $parameters['date_end'];
        $filterType     = $parameters['filter_type'];

        if ($filterType == DateRangeType::TYPE_NOT_BETWEEN) {
            $this->applyFilterNotBetween($queryBuilder, $dateStartValue, $dateEndValue, $field);
        } else {
            $this->applyFilterBetween($queryBuilder, $dateStartValue, $dateEndValue, $field);
        }
    }

    /**
     * @param ProxyQueryInterface $queryBuilder
     * @param string $dateStartValue
     * @param string $dateEndValue
     * @param string $field
     */
    protected function applyFilterBetween(
        ProxyQueryInterface $queryBuilder,
        $dateStartValue,
        $dateEndValue,
        $field
    ) {
        if ($dateStartValue) {
            $this->applyFlexibleFilter($queryBuilder, $field, $dateStartValue, '>=');
        }

        if ($dateEndValue) {
            $this->applyFlexibleFilter($queryBuilder, $field, $dateEndValue, '<=');
        }
    }

    /**
     * @param ProxyQueryInterface $queryBuilder
     * @param string $dateStartValue
     * @param string $dateEndValue
     * @param string $field
     */
    protected function applyFilterNotBetween(
        ProxyQueryInterface $queryBuilder,
        $dateStartValue,
        $dateEndValue,
        $field
    ) {
        $values = array();
        $operators = array();

        if ($dateStartValue) {
            $values['from'] = $dateStartValue;
            $operators['from'] = '<';
        }

        if ($dateEndValue) {
            $values['to'] = $dateEndValue;
            $operators['to'] = '>';
        }

        if ($values && $operators) {
            $this->applyFlexibleFilter($queryBuilder, $field, $values, $operators);
        }
    }
}
