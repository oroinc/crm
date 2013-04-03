<?php

namespace Oro\Bundle\GridBundle\Filter\ORM;

use Doctrine\DBAL\Query\QueryBuilder;
use Sonata\AdminBundle\Form\Type\Filter\ChoiceType;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;

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

        if (!isset($data['type'])) {
            $data['type'] = ChoiceType::TYPE_CONTAINS;
        }

        $operator = $this->getOperator((int) $data['type']);
        if (!$operator) {
            $operator = $this->getOperator(ChoiceType::TYPE_CONTAINS);
        }

        // c.name > '1' => c.name OPERATOR :FIELDNAME
        $parameterName = $this->getNewParameterName($queryBuilder);
        if ($this->isComplexField()) {
            $this->applyHaving($queryBuilder, sprintf('%s %s :%s', $field, $operator, $parameterName));
        } else {
            $this->applyWhere($queryBuilder, sprintf('%s.%s %s :%s', $alias, $field, $operator, $parameterName));
        }

        /** @var $queryBuilder QueryBuilder */
        if ($data['type'] == ChoiceType::TYPE_EQUAL) {
            $queryBuilder->setParameter($parameterName, $data['value']);
        } else {
            $queryBuilder->setParameter($parameterName, sprintf($this->getOption('format'), $data['value']));
        }
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    public function getOperator($type)
    {
        $choices = array(
            ChoiceType::TYPE_CONTAINS     => 'LIKE',
            ChoiceType::TYPE_NOT_CONTAINS => 'NOT LIKE',
            ChoiceType::TYPE_EQUAL        => '=',
        );

        if (isset($choices[$type])) {
            return $choices[$type];
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
