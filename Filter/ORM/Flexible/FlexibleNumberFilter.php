<?php

namespace Oro\Bundle\GridBundle\Filter\ORM\Flexible;

use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Form\Type\Filter\NumberType;
use Oro\Bundle\GridBundle\Filter\ORM\NumberFilter;

class FlexibleNumberFilter extends AbstractFlexibleChildFilter
{
    /**
     * @var string
     */
    protected $parentFilterClass = 'Oro\\Bundle\\GridBundle\\Filter\\ORM\\NumberFilter';

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
        $operator = $this->getOperator($type, NumberType::TYPE_EQUAL);

        // apply filter
        $this->applyFlexibleFilter($proxyQuery, $field, $data['value'], $operator);
    }

    /**
     * Get operator as string
     *
     * @param string $type
     * @param mixed $default
     * @return bool
     */
    public function getOperator($type, $default = null)
    {
        return $this->parentFilter->getOperator($type, $default);
    }
}
