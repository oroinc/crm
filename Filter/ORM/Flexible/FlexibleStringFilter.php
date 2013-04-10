<?php

namespace Oro\Bundle\GridBundle\Filter\ORM\Flexible;

use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Form\Type\Filter\ChoiceType;
use Oro\Bundle\GridBundle\Filter\ORM\StringFilter;

class FlexibleStringFilter extends AbstractFlexibleFilter
{
    /**
     * @var string
     */
    protected $parentFilterClass = 'Oro\\Bundle\\GridBundle\\Filter\\ORM\\StringFilter';

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
        $type = isset($data['type']) ? $data['type'] : false;
        if ($type == ChoiceType::TYPE_EQUAL) {
            $value = $data['value'];
        } else {
            $value = sprintf($this->getOption('format'), $data['value']);
        }

        // process operator
        $operator = $this->getOperator($type, ChoiceType::TYPE_CONTAINS);

        // apply filter
        $this->applyFlexibleFilter($proxyQuery, $field, $value, $operator);
    }

    /**
     * Get operator as string
     *
     * @param int $type
     * @param mixed $default
     * @return int|bool
     */
    public function getOperator($type, $default = null)
    {
        return $this->parentFilter->getOperator($type, $default);
    }
}
