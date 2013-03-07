<?php

namespace Oro\Bundle\GridBundle\Filter\ORM;

use Symfony\Component\Translation\TranslatorInterface;
use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Form\Type\Filter\NumberType;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;

class NumberFilter extends AbstractFilter implements FilterInterface
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

        $operator = $this->getOperator($type);

        if (!$operator) {
            $operator = '=';
        }

        // c.name > '1' => c.name OPERATOR :FIELDNAME
        $parameterName = $this->getNewParameterName($queryBuilder);
        if ($this->isComplexField()) {
            $this->applyHaving($queryBuilder, sprintf('%s %s :%s', $field, $operator, $parameterName));
        } else {
            $this->applyWhere($queryBuilder, sprintf('%s.%s %s :%s', $alias, $field, $operator, $parameterName));
        }
        $queryBuilder->setParameter($parameterName, $data['value']);
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    protected function getOperator($type)
    {
        $choices = array(
            NumberType::TYPE_EQUAL            => '=',
            NumberType::TYPE_GREATER_EQUAL    => '>=',
            NumberType::TYPE_GREATER_THAN     => '>',
            NumberType::TYPE_LESS_EQUAL       => '<=',
            NumberType::TYPE_LESS_THAN        => '<',
        );

        return isset($choices[$type]) ? $choices[$type] : false;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions()
    {
        return array();
    }
}
