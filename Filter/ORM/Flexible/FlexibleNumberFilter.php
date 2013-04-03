<?php

namespace Oro\Bundle\GridBundle\Filter\ORM\Flexible;

use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Form\Type\Filter\NumberType;
use Oro\Bundle\GridBundle\Filter\ORM\NumberFilter;

class FlexibleNumberFilter extends AbstractFlexibleFilter
{
    /**
     * @var NumberFilter
     */
    protected $parentFilter;

    /**
     * {@inheritdoc}
     */
    public function filter(ProxyQueryInterface $proxyQuery, $alias, $field, $data)
    {
        if (!$data || !is_array($data) || !array_key_exists('value', $data) || !is_numeric($data['value'])) {
            return;
        }

        $type = isset($data['type']) ? $data['type'] : false;

        $operator = $this->getOperator($type);
        if (!$operator) {
            $operator = $this->getOperator(NumberType::TYPE_EQUAL);
        }

        // apply filter
        $this->applyFlexibleFilter($proxyQuery, $field, $data['value'], $operator);
    }

    /**
     * Flexible number filter operator
     *
     * @param string $type
     * @return string
     */
    public function getOperator($type)
    {
        if ($this->parentFilter) {
            return $this->parentFilter->getOperator($type);
        }

        return null;
    }
}
