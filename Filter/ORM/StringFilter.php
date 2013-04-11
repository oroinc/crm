<?php

namespace Oro\Bundle\GridBundle\Filter\ORM;

use Doctrine\DBAL\Query\QueryBuilder;

use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Form\Type\Filter\ChoiceType;

class StringFilter extends AbstractFilter
{
    /**
     * {@inheritdoc}
     */
    public function getRenderSettings()
    {
        return array('oro_grid_type_filter_choice', array(
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
            ChoiceType::TYPE_CONTAINS
                => $this->translator->trans('label_type_contains', array(), 'SonataAdminBundle'),
            ChoiceType::TYPE_NOT_CONTAINS
                => $this->translator->trans('label_type_not_contains', array(), 'SonataAdminBundle'),
            ChoiceType::TYPE_EQUAL
                => $this->translator->trans('label_type_equals', array(), 'SonataAdminBundle'),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function filter(ProxyQueryInterface $queryBuilder, $alias, $field, $data)
    {
        if (!$data || !is_array($data) || !array_key_exists('value', $data)) {
            return;
        }

        $data['value'] = trim($data['value']);

        if (strlen($data['value']) == 0) {
            return;
        }

        $type = isset($data['type']) ? $data['type'] : false;
        $operator = $this->getOperator($type, ChoiceType::TYPE_CONTAINS);
        $parameterName = $this->getNewParameterName($queryBuilder);

        $this->applyFilterToClause(
            $queryBuilder,
            $this->createCompareFieldExpression($field, $alias, $operator, $parameterName)
        );

        /** @var $queryBuilder QueryBuilder */
        if ($type == ChoiceType::TYPE_EQUAL) {
            $queryBuilder->setParameter($parameterName, $data['value']);
        } else {
            $queryBuilder->setParameter($parameterName, sprintf($this->getOption('format'), $data['value']));
        }
    }

    /**
     * Get operator as string
     *
     * @param int $type
     * @param int|null $default
     * @return int|bool
     */
    public function getOperator($type, $default = null)
    {
        $type = (int) $type;

        $choices = array(
            ChoiceType::TYPE_CONTAINS     => 'LIKE',
            ChoiceType::TYPE_NOT_CONTAINS => 'NOT LIKE',
            ChoiceType::TYPE_EQUAL        => '=',
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
        return array(
            'format' => '%%%s%%'
        );
    }
}
