<?php

namespace Oro\Bundle\GridBundle\Filter\ORM;

use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\Query\Expr;

use Sonata\AdminBundle\Form\Type\Filter\DateRangeType;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;

abstract class AbstractDateFilter extends AbstractFilter
{
    /**
     * Date value format
     */
    const VALUE_FORMAT = '/^\d{4}-\d{2}-\d{2}$/';

    /**
     * Flag indicating that filter will filter by datetime instead by date
     * @var boolean
     */
    protected $time = false;

    /**
     * {@inheritdoc}
     */
    public function filter(ProxyQueryInterface $queryBuilder, $alias, $field, $data)
    {
        if (!$this->isParametersCorrect($data)) {
            return;
        }

        $parameters     = $this->getFilterParameters($data);
        $dateStartValue = $parameters['date_start'];
        $dateEndValue   = $parameters['date_end'];
        $filterType     = $parameters['filter_type'];

        $startDateParameterName = $this->getNewParameterName($queryBuilder);
        $endDateParameterName   = $this->getNewParameterName($queryBuilder);

        if ($filterType == DateRangeType::TYPE_NOT_BETWEEN) {
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
            $queryBuilder->setParameter($startDateParameterName, $dateStartValue);
        }
        if ($dateEndValue) {
            $queryBuilder->setParameter($endDateParameterName, $dateEndValue);
        }
    }

    /**
     * Get filter parameters using data
     *
     * @param array $data
     * @return array
     */
    public function getFilterParameters($data)
    {
        $dateStartValue = trim($data['value']['start']);
        $dateEndValue   = trim($data['value']['end']);

        if (!isset($data['type']) || !is_numeric($data['type'])) {
            $filterType = DateRangeType::TYPE_BETWEEN;
        } else {
            $filterType = $data['type'];
        }

        return array(
            'date_start'  => $dateStartValue,
            'date_end'    => $dateEndValue,
            'filter_type' => $filterType
        );
    }

    /**
     * @param array $data
     * @return bool
     */
    public function isParametersCorrect($data)
    {
        // check data sanity
        if (!$data || !is_array($data) || !array_key_exists('value', $data)) {
            return false;
        }

        // additional data check for ranged items
        if (!array_key_exists('start', $data['value']) || !array_key_exists('end', $data['value'])) {
            return false;
        }

        // check date format
        if (!$this->isDateCorrect($data['value']['start']) && !$this->isDateCorrect($data['value']['end'])) {
            return false;
        }

        return true;
    }

    /**
     * Checks if date matches format or empty
     *
     * @param string $date
     * @return bool
     */
    protected function isDateCorrect($date)
    {
        $date = trim($date);

        if ($date && !preg_match(static::VALUE_FORMAT, $date)) {
            return false;
        }

        return true;
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

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions()
    {
        return array(
            'input_type' => 'datetime'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getRenderSettings()
    {
        $name = 'oro_grid_type_filter_date';

        if ($this->time) {
            $name .= 'time';
        }

        $name .= '_range';

        return array($name, array(
            'field_type'    => $this->getFieldType(),
            'field_options' => $this->getFieldOptions(),
            'label'         => $this->getLabel(),
        ));
    }
}
