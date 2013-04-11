<?php

namespace Oro\Bundle\GridBundle\Filter\ORM;

use Doctrine\ORM\QueryBuilder;

use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Form\Type\Filter\NumberType;

class NumberFilter extends AbstractFilter
{
    /**
     * {@inheritdoc}
     */
    public function getRenderSettings()
    {
        return array('oro_grid_type_filter_number', array(
            'field_type'    => $this->getFieldType(),
            'field_options' => $this->getFieldOptions(),
            'label'         => $this->getLabel()
        ));
    }

    /**
     * Get operator types
     *
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

    /**
     * {@inheritdoc}
     */
    public function filter(ProxyQueryInterface $queryBuilder, $alias, $field, $data)
    {
        if (!$data || !is_array($data) || !array_key_exists('value', $data) || !is_numeric($data['value'])) {
            return;
        }

        $type = isset($data['type']) ? $data['type'] : false;
        $operator = $this->getOperator($type, NumberType::TYPE_EQUAL);

        // c.name > '1' => c.name OPERATOR :FIELDNAME
        $parameterName = $this->getNewParameterName($queryBuilder);

        $this->applyFilterToClause(
            $queryBuilder,
            $this->createCompareFieldExpression($field, $alias, $operator, $parameterName)
        );

        $queryBuilder->setParameter($parameterName, $data['value']);
    }

    /**
     * @param int $type
     * @param int|null $default
     * @return int|bool
     */
    public function getOperator($type, $default = null)
    {
        $type = (int) $type;

        $choices = array(
            NumberType::TYPE_EQUAL         => '=',
            NumberType::TYPE_GREATER_EQUAL => '>=',
            NumberType::TYPE_GREATER_THAN  => '>',
            NumberType::TYPE_LESS_EQUAL    => '<=',
            NumberType::TYPE_LESS_THAN     => '<',
        );

        if (isset($choices[$type])) {
            return $choices[$type];
        }

        if (!is_null($default) && isset($choices[$default])) {
            return $choices[$default];
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions()
    {
        return array();
    }
}
