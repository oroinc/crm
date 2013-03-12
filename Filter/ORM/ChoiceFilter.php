<?php

namespace Oro\Bundle\GridBundle\Filter\ORM;

use Sonata\AdminBundle\Form\Type\Filter\ChoiceType;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;
use Doctrine\DBAL\Query\QueryBuilder;

class ChoiceFilter extends AbstractFilter implements FilterInterface
{
    /**
     * {@inheritdoc}
     */
    public function getRenderSettings()
    {
        $renderSettings    = parent::getRenderSettings();
        $renderSettings[0] = 'oro_grid_type_filter_default';
        return $renderSettings;
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
        if (!$data || !is_array($data) || !array_key_exists('type', $data) || !array_key_exists('value', $data)) {
            return;
        }

        if (is_array($data['value'])) {
            if (count($data['value']) == 0) {
                return;
            }

            if (in_array('all', $data['value'], true)) {
                return;
            }

            $this->applyFilterWithArrayValues($queryBuilder, $data['type'], $alias, $field, $data['value']);
        } else {
            if ($data['value'] === '' || $data['value'] === null
                || $data['value'] === false || $data['value'] === 'all'
            ) {
                return;
            }

            $parameterName = $this->getNewParameterName($queryBuilder);

            if ($data['type'] == ChoiceType::TYPE_NOT_CONTAINS) {
                $this->applyWhere($queryBuilder, sprintf('%s.%s <> :%s', $alias, $field, $parameterName));
            } else {
                $this->applyWhere($queryBuilder, sprintf('%s.%s = :%s', $alias, $field, $parameterName));
            }

            /** @var $queryBuilder QueryBuilder */
            $queryBuilder->setParameter($parameterName, $data['value']);
        }
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param string $filterType
     * @param string $alias
     * @param string $field
     * @param string $value
     */
    protected function applyFilterWithArrayValues($queryBuilder, $filterType, $alias, $field, $value)
    {
        if ($filterType == ChoiceType::TYPE_NOT_CONTAINS) {
            if ($this->isComplexField()) {
                $this->applyHaving(
                    $queryBuilder,
                    $queryBuilder->expr()->notIn(sprintf('%s', $field), $value)
                );
            } else {
                $this->applyWhere(
                    $queryBuilder,
                    $queryBuilder->expr()->notIn(sprintf('%s.%s', $alias, $field), $value)
                );
            }
        } else {
            if ($this->isComplexField()) {
                $this->applyHaving(
                    $queryBuilder,
                    $queryBuilder->expr()->in(sprintf('%s', $field), $value)
                );
            } else {
                $this->applyWhere(
                    $queryBuilder,
                    $queryBuilder->expr()->in(sprintf('%s.%s', $alias, $field), $value)
                );
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions()
    {
        return array();
    }
}
