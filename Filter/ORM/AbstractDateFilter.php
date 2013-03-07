<?php

namespace Oro\Bundle\GridBundle\Filter\ORM;

use Sonata\AdminBundle\Form\Type\Filter\DateType;
use Sonata\AdminBundle\Form\Type\Filter\DateRangeType;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;

abstract class AbstractDateFilter extends AbstractFilter
{
    /**
     * Flag indicating that filter will have range
     * @var boolean
     */
    protected $range = false;

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
        //check data sanity
        if (!$data || !is_array($data) || !array_key_exists('value', $data)) {
            return;
        }

        if ($this->range) {
            //additional data check for ranged items
            if (!array_key_exists('start', $data['value']) || !array_key_exists('end', $data['value'])) {
                return;
            }

            if (!$data['value']['start'] || !$data['value']['end']) {
                return;
            }

            //transform types
            if ($this->getOption('input_type') == 'timestamp') {
                $data['value']['start'] = $data['value']['start'] instanceof \DateTime
                    ? $data['value']['start']->getTimestamp() : 0;
                $data['value']['end'] = $data['value']['end'] instanceof \DateTime
                    ? $data['value']['end']->getTimestamp() : 0;
            }

            //default type for range filter
            $data['type'] = !isset($data['type']) || !is_numeric($data['type'])
                ?  DateRangeType::TYPE_BETWEEN : $data['type'];

            $startDateParameterName = $this->getNewParameterName($queryBuilder);
            $endDateParameterName = $this->getNewParameterName($queryBuilder);

            if ($data['type'] == DateRangeType::TYPE_NOT_BETWEEN) {
                if ($this->isComplexField()) {
                    $this->applyHaving(
                        $queryBuilder,
                        sprintf(
                            '%s < :%s OR %s > :%s',
                            $field,
                            $startDateParameterName,
                            $field,
                            $endDateParameterName
                        )
                    );
                } else {
                    $this->applyWhere(
                        $queryBuilder,
                        sprintf(
                            '%s.%s < :%s OR %s.%s > :%s',
                            $alias,
                            $field,
                            $startDateParameterName,
                            $alias,
                            $field,
                            $endDateParameterName
                        )
                    );
                }
            } else {
                if ($this->isComplexField()) {
                    $this->applyHaving(
                        $queryBuilder,
                        sprintf('%s %s :%s', $field, '>=', $startDateParameterName)
                    );
                    $this->applyHaving(
                        $queryBuilder,
                        sprintf('%s %s :%s', $field, '<=', $endDateParameterName)
                    );
                } else {
                    $this->applyWhere(
                        $queryBuilder,
                        sprintf('%s.%s %s :%s', $alias, $field, '>=', $startDateParameterName)
                    );
                    $this->applyWhere(
                        $queryBuilder,
                        sprintf('%s.%s %s :%s', $alias, $field, '<=', $endDateParameterName)
                    );
                }
            }

            $queryBuilder->setParameter($startDateParameterName, $data['value']['start']);
            $queryBuilder->setParameter($endDateParameterName, $data['value']['end']);
        } else {

            if (!$data['value']) {
                return;
            }

            //default type for simple filter
            $data['type'] = !isset($data['type']) || !is_numeric($data['type']) ? DateType::TYPE_EQUAL : $data['type'];

            //just find an operator and apply query
            $operator = $this->getOperator($data['type']);

            //transform types
            if ($this->getOption('input_type') == 'timestamp') {
                $data['value'] = $data['value'] instanceof \DateTime ? $data['value']->getTimestamp() : 0;
            }

            //null / not null only check for col
            if (in_array($operator, array('NULL', 'NOT NULL'))) {
                if ($this->isComplexField()) {
                    $this->applyHaving($queryBuilder, sprintf('%s IS %s ', $field, $operator));
                } else {
                    $this->applyWhere($queryBuilder, sprintf('%s.%s IS %s ', $alias, $field, $operator));
                }
            } else {
                $parameterName = $this->getNewParameterName($queryBuilder);

                if ($this->isComplexField()) {
                    $this->applyHaving(
                        $queryBuilder,
                        sprintf('%s %s :%s', $field, $operator, $parameterName)
                    );
                } else {
                    $this->applyWhere(
                        $queryBuilder,
                        sprintf('%s.%s %s :%s', $alias, $field, $operator, $parameterName)
                    );
                }
                $queryBuilder->setParameter($parameterName, $data['value']);
            }
        }
    }

    /**
     * Resolves DataType:: constants to SQL operators
     *
     * @param integer $type
     *
     * @return string
     */
    protected function getOperator($type)
    {
        $type = intval($type);

        $choices = array(
            DateType::TYPE_EQUAL            => '=',
            DateType::TYPE_GREATER_EQUAL    => '>=',
            DateType::TYPE_GREATER_THAN     => '>',
            DateType::TYPE_LESS_EQUAL       => '<=',
            DateType::TYPE_LESS_THAN        => '<',
            DateType::TYPE_NULL             => 'NULL',
            DateType::TYPE_NOT_NULL         => 'NOT NULL',
        );

        return isset($choices[$type]) ? $choices[$type] : '=';
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

        if ($this->range) {
            $name .= '_range';
        }

        return array($name, array(
            'field_type'    => $this->getFieldType(),
            'field_options' => $this->getFieldOptions(),
            'label'         => $this->getLabel(),
        ));
    }
}
