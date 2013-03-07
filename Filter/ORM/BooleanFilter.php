<?php

namespace Oro\Bundle\GridBundle\Filter\ORM;

use Symfony\Component\Translation\TranslatorInterface;
use Sonata\AdminBundle\Form\Type\BooleanType;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;

class BooleanFilter extends AbstractFilter implements FilterInterface
{
    /**
     * {@inheritdoc}
     */
    public function getRenderSettings()
    {
        return array('oro_grid_type_filter_default', array(
            'field_type'    => $this->getFieldType(),
            'field_options' => $this->getFieldOptions(),
            'operator_type' => 'hidden',
            'operator_options' => array(),
            'label'         => $this->getLabel()
        ));
    }

    /**
     * @return array
     */
    public function getValueOptions()
    {
        return array(
            BooleanType::TYPE_YES => $this->translator->trans('label_type_yes', array(), 'SonataAdminBundle'),
            BooleanType::TYPE_NO  => $this->translator->trans('label_type_no', array(), 'SonataAdminBundle')
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
            $values = array();
            foreach ($data['value'] as $v) {
                if (!in_array($v, array(BooleanType::TYPE_NO, BooleanType::TYPE_YES))) {
                    continue;
                }

                $values[] = ($v == BooleanType::TYPE_YES) ? 1 : 0;
            }

            if (count($values) == 0) {
                return;
            }

            if ($this->isComplexField()) {
                $this->applyHaving($queryBuilder, $queryBuilder->expr()->in(sprintf('%s', $field), $values));
            } else {
                $this->applyWhere($queryBuilder, $queryBuilder->expr()->in(sprintf('%s.%s', $alias, $field), $values));
            }
        } else {

            if (!in_array($data['value'], array(BooleanType::TYPE_NO, BooleanType::TYPE_YES))) {
                return;
            }

            $parameterName = $this->getNewParameterName($queryBuilder);
            if ($this->isComplexField()) {
                $this->applyHaving($queryBuilder, sprintf('%s = :%s', $field, $parameterName));
            } else {
                $this->applyWhere($queryBuilder, sprintf('%s.%s = :%s', $alias, $field, $parameterName));
            }
            $queryBuilder->setParameter($parameterName, ($data['value'] == BooleanType::TYPE_YES) ? 1 : 0);
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
