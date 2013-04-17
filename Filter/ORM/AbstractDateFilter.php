<?php

namespace Oro\Bundle\GridBundle\Filter\ORM;

use Doctrine\DBAL\Query\QueryBuilder;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\FilterBundle\Form\Type\Filter\DateRangeFilterType;

abstract class AbstractDateFilter extends AbstractFilter
{
    /**
     * DateTime object as string format
     */
    const DATETIME_FORMAT = 'Y-m-d';


    /**
     * {@inheritdoc}
     */
    public function filter(ProxyQueryInterface $queryBuilder, $alias, $field, $data)
    {
        $data = $this->parseData($data);
        if (!$data) {
            return;
        }

        /** @var $dateStartValue \DateTime */
        $dateStartValue = $data['date_start'];
        /** @var $dateEndValue \DateTime */
        $dateEndValue = $data['date_end'];
        $operatorType = $data['operator_type'];

        $startDateParameterName = $this->getNewParameterName($queryBuilder);
        $endDateParameterName = $this->getNewParameterName($queryBuilder);

        if ($operatorType == DateRangeFilterType::TYPE_NOT_BETWEEN) {
            $this->applyFilterNotBetween(
                $queryBuilder,
                $dateStartValue,
                $dateEndValue,
                $startDateParameterName,
                $endDateParameterName,
                $alias,
                $field
            );
        } else {
            $this->applyFilterBetween(
                $queryBuilder,
                $dateStartValue,
                $dateEndValue,
                $startDateParameterName,
                $endDateParameterName,
                $alias,
                $field
            );
        }

        /** @var $queryBuilder QueryBuilder */
        if ($dateStartValue) {
            $queryBuilder->setParameter($startDateParameterName, $dateStartValue->format(static::DATETIME_FORMAT));
        }
        if ($dateEndValue) {
            $queryBuilder->setParameter($endDateParameterName, $dateEndValue->format(static::DATETIME_FORMAT));
        }
    }

    /**
     * @param mixed $data
     * @return array|bool
     */
    public function parseData($data)
    {
        if (!is_array($data) || !array_key_exists('value', $data) || !is_array($data['value'])) {
            return false;
        }

        $data['value']['start'] = isset($data['value']['start']) ? $data['value']['start'] : null;
        $data['value']['end'] = isset($data['value']['end']) ? $data['value']['end'] : null;

        if (!$data['value']['start'] && !$data['value']['end']) {
            return false;
        }

        // check start date
        if ($data['value']['start'] && !$data['value']['start'] instanceof \DateTime) {
            return false;
        }

        // check end date
        if ($data['value']['end'] && !$data['value']['end'] instanceof \DateTime) {
            return false;
        }

        $data['type'] = isset($data['type']) ? $data['type'] : null;

        if ($data['type'] != DateRangeFilterType::TYPE_NOT_BETWEEN) {
            $data['type'] = DateRangeFilterType::TYPE_BETWEEN;
        }

        return array(
            'date_start' => $data['value']['start'],
            'date_end' => $data['value']['end'],
            'operator_type' => $data['type']
        );
    }

    /**
     * Apply expression using "between" filtering
     *
     * @param ProxyQueryInterface $queryBuilder
     * @param string $dateStartValue
     * @param string $dateEndValue
     * @param string $startDateParameterName
     * @param string $endDateParameterName
     * @param string $alias
     * @param string $field
     */
    protected function applyFilterBetween(
        $queryBuilder,
        $dateStartValue,
        $dateEndValue,
        $startDateParameterName,
        $endDateParameterName,
        $alias,
        $field
    ) {
        if ($dateStartValue) {
            $this->applyFilterToClause(
                $queryBuilder,
                $this->createCompareFieldExpression($field, $alias, '>=', $startDateParameterName)
            );
        }

        if ($dateEndValue) {
            $this->applyFilterToClause(
                $queryBuilder,
                $this->createCompareFieldExpression($field, $alias, '<=', $endDateParameterName)
            );
        }
    }

    /**
     * Apply expression using "not between" filtering
     *
     * @param ProxyQueryInterface $queryBuilder
     * @param string $dateStartValue
     * @param string $dateEndValue
     * @param string $startDateParameterName
     * @param string $endDateParameterName
     * @param string $alias
     * @param string $field
     */
    protected function applyFilterNotBetween(
        $queryBuilder,
        $dateStartValue,
        $dateEndValue,
        $startDateParameterName,
        $endDateParameterName,
        $alias,
        $field
    ) {
        $orExpression = $this->getExpressionFactory()->orX();

        if ($dateStartValue) {
            $orExpression->add($this->createCompareFieldExpression($field, $alias, '<', $startDateParameterName));
        }

        if ($dateEndValue) {
            $orExpression->add($this->createCompareFieldExpression($field, $alias, '>', $endDateParameterName));
        }

        $this->applyFilterToClause($queryBuilder, $orExpression);
    }
}
