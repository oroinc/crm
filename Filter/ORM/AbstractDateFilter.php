<?php

namespace Oro\Bundle\GridBundle\Filter\ORM;

use Sonata\AdminBundle\Form\Type\Filter\DateRangeType;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Doctrine\DBAL\Query\QueryBuilder;

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
        if (!$this->isDateCorrect($data['value']['start'], $data['value']['end'])) {
            return false;
        }

        return true;
    }

    /**
     * @param string $dateStartValue
     * @param string $dateEndValue
     * @return bool
     */
    protected function isDateCorrect($dateStartValue, $dateEndValue)
    {
        $dateStartValue = trim($dateStartValue);
        $dateEndValue   = trim($dateEndValue);

        if ($dateStartValue && !preg_match(static::VALUE_FORMAT, $dateStartValue)) {
            $dateStartValue = null;
        }
        if ($dateEndValue && !preg_match(static::VALUE_FORMAT, $dateEndValue)) {
            $dateEndValue = null;
        }

        if (!$dateStartValue && !$dateEndValue) {
            return false;
        }

        return true;
    }

    /**
     * @param ProxyQueryInterface $queryBuilder
     * @param string $dateStartValue
     * @param string $dateEndValue
     * @param string $startDateParameterName
     * @param string $endDateParameterName
     * @param string $alias
     * @param string $field
     */
    protected function applyFilterBetween(
        ProxyQueryInterface $queryBuilder,
        $dateStartValue,
        $dateEndValue,
        $startDateParameterName,
        $endDateParameterName,
        $alias,
        $field
    ) {
        if ($dateStartValue) {
            if ($this->isComplexField()) {
                $this->applyHaving($queryBuilder, sprintf('%s %s :%s', $field, '>=', $startDateParameterName));
            } else {
                $this->applyWhere(
                    $queryBuilder,
                    sprintf('%s.%s %s :%s', $alias, $field, '>=', $startDateParameterName)
                );
            }
        }

        if ($dateEndValue) {
            if ($this->isComplexField()) {
                $this->applyHaving($queryBuilder, sprintf('%s %s :%s', $field, '<=', $endDateParameterName));
            } else {
                $this->applyWhere(
                    $queryBuilder,
                    sprintf('%s.%s %s :%s', $alias, $field, '<=', $endDateParameterName)
                );
            }
        }
    }

    /**
     * @param ProxyQueryInterface $queryBuilder
     * @param string $dateStartValue
     * @param string $dateEndValue
     * @param string $startDateParameterName
     * @param string $endDateParameterName
     * @param string $alias
     * @param string $field
     */
    protected function applyFilterNotBetween(
        ProxyQueryInterface $queryBuilder,
        $dateStartValue,
        $dateEndValue,
        $startDateParameterName,
        $endDateParameterName,
        $alias,
        $field
    ) {
        $conditionParts = array();

        if ($dateStartValue) {
            if ($this->isComplexField()) {
                $conditionParts[] = sprintf('%s < :%s', $field, $startDateParameterName);
            } else {
                $conditionParts[] = sprintf('%s.%s < :%s', $alias, $field, $startDateParameterName);
            }
        }

        if ($dateEndValue) {
            if ($this->isComplexField()) {
                $conditionParts[] = sprintf('%s > :%s', $field, $endDateParameterName);
            } else {
                $conditionParts[] = sprintf('%s.%s > :%s', $alias, $field, $endDateParameterName);
            }
        }

        if ($this->isComplexField()) {
            $this->applyHaving($queryBuilder, implode(' OR ', $conditionParts));
        } else {
            $this->applyWhere($queryBuilder, implode(' OR ', $conditionParts));
        }
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
