<?php

namespace Oro\Bundle\GridBundle\Filter\ORM;

use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;

class CallbackFilter extends AbstractFilter implements FilterInterface
{
    /**
     * {@inheritdoc}
     */
    public function getRenderSettings()
    {
        return array('oro_grid_type_filter_default', array(
            'field_type'    => $this->getFieldType(),
            'field_options' => $this->getFieldOptions(),
            'operator_type' => $this->getOption('operator_type'),
            'operator_options' => $this->getOption('operator_options'),
            'label'         => $this->getLabel()
        ));
    }

    /**
     * @param ProxyQueryInterface $queryBuilder
     * @param array $value
     * @return array
     */
    protected function association(ProxyQueryInterface $queryBuilder, $value)
    {
        $alias = $this->getOption('entity_alias')
            ?: $queryBuilder->getRootAlias();

        return array($alias, false);
    }

    /**
     * {@inheritdoc}
     */
    public function filter(ProxyQueryInterface $queryBuilder, $alias, $field, $data)
    {
        if (!is_callable($this->getOption('callback'))) {
            throw new \RuntimeException(
                sprintf('Please provide a valid callback option "filter" for field "%s"', $this->getName())
            );
        }

        $this->active = call_user_func($this->getOption('callback'), $queryBuilder, $alias, $field, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions()
    {
        return array(
            'callback'    => null,
            'field_type'  => 'text',
            'operator_type' => 'hidden',
            'operator_options' => array()
        );
    }
}
