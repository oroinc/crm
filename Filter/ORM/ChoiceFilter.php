<?php

namespace Oro\Bundle\GridBundle\Filter\ORM;

use Doctrine\DBAL\Query\QueryBuilder;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\FilterBundle\Form\Type\Filter\ChoiceFilterType;

class ChoiceFilter extends AbstractFilter
{
    /**
     * {@inheritdoc}
     */
    public function filter(ProxyQueryInterface $queryBuilder, $alias, $field, $data)
    {
        $data = $this->parseData($data);
        if (!$data) {
            return;
        }

        $operator = $this->getOperator($data['type']);

        /** @var $queryBuilder QueryBuilder */
        if ('=' == $operator) {
            $parameterName = $this->getNewParameterName($queryBuilder);
            $this->applyFilterToClause(
                $queryBuilder,
                $this->createCompareFieldExpression($field, $alias, $operator, $parameterName)
            );
            $queryBuilder->setParameter($parameterName, $data['value']);
        } else {
            $expression = $this->getExpressionFactory()->in(
                $this->createFieldExpression($field, $alias),
                $data['value']
            );
            if ('NOT IN' == $operator) {
                $expression = $this->getExpressionFactory()->not($expression);
            }
            $this->applyFilterToClause($queryBuilder, $expression);
        }
    }

    /**
     * @param mixed $data
     * @return array|bool
     */
    public function parseData($data)
    {
        if (!is_array($data) || !array_key_exists('value', $data) || !$data['value']) {
            return false;
        }

        $data['type'] = isset($data['type']) ? $data['type'] : null;

        return $data;
    }

    /**
     * Get operator string
     *
     * @param int $type
     * @return string
     */
    public function getOperator($type)
    {
        $type = (int)$type;

        $operatorTypes = array(
            ChoiceFilterType::TYPE_CONTAINS     => 'IN',
            ChoiceFilterType::TYPE_NOT_CONTAINS => 'NOT IN',
            ChoiceFilterType::TYPE_EQUAL        => '=',
        );

        return isset($operatorTypes[$type]) ? $operatorTypes[$type] : '=';
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions()
    {
        return array(
            'form_type' => ChoiceFilterType::NAME
        );
    }
}
