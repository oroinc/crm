<?php

namespace Oro\Bundle\GridBundle\Filter\ORM\Flexible;

use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;

class FlexibleStringFilter extends AbstractFlexibleFilter
{
    /**
     * {@inheritdoc}
     */
    public function filter(ProxyQueryInterface $queryBuilder, $alias, $field, $value)
    {
        // TODO: Implement filter() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions()
    {
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function getRenderSettings()
    {
        return array('oro_grid_type_filter_default', array(
            'label' => $this->getLabel()
        ));
    }
}
