<?php

namespace Oro\Bundle\GridBundle\Builder;

use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Datagrid\DatagridInterface;

interface DatagridBuilderInterface
{
    /**
     * @param DatagridInterface $datagrid
     * @param FieldDescriptionInterface $fieldDescription
     * @return void
     */
    public function addFilter(
        DatagridInterface $datagrid,
        FieldDescriptionInterface $fieldDescription = null
    );

    /**
     * @param ProxyQueryInterface $query
     * @param FieldDescriptionCollection $fieldCollection
     * @param array $values
     * @return DatagridInterface
     */
    public function getBaseDatagrid(
        ProxyQueryInterface $query,
        FieldDescriptionCollection $fieldCollection,
        array $values = array()
    );
}
