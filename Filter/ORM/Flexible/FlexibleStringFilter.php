<?php

namespace Oro\Bundle\GridBundle\Filter\ORM\Flexible;

use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\Type\Filter\ChoiceType;
use Oro\Bundle\GridBundle\Filter\ORM\StringFilter;

class FlexibleStringFilter extends AbstractFlexibleFilter
{
    /**
     * @var StringFilter
     */
    protected $parentFilter;

    /**
     * {@inheritdoc}
     */
    public function filter(ProxyQueryInterface $proxyQuery, $alias, $field, $data)
    {
        if (!$data || !is_array($data) || !array_key_exists('value', $data)) {
            return;
        }

        $data['value'] = trim($data['value']);

        if (strlen($data['value']) == 0) {
            return;
        }

        // process type
        $data['type'] = !isset($data['type']) ? ChoiceType::TYPE_CONTAINS : $data['type'];
        if ($data['type'] == ChoiceType::TYPE_EQUAL) {
            $value = $data['value'];
        } else {
            $value = sprintf($this->getOption('format'), $data['value']);
        }

        // process operator
        $operator = $this->getOperator((int) $data['type']);
        if (!$operator) {
            $operator = $this->getOperator(ChoiceType::TYPE_CONTAINS);
        }

        // apply filter
        $this->applyFlexibleFilter($proxyQuery, $field, $value, $operator);
    }

    /**
     * @return array
     */
    public function getTypeOptions()
    {
        return $this->parentFilter->getTypeOptions();
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    public function getOperator($type)
    {
        return $this->parentFilter->getOperator($type);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions()
    {
        return $this->parentFilter->getDefaultOptions();
    }

    /**
     * {@inheritdoc}
     */
    public function getRenderSettings()
    {
        return $this->parentFilter->getRenderSettings();
    }
}
