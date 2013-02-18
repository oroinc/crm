<?php

namespace Oro\Bundle\GridBundle\Builder;

use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\GridBundle\Datagrid\ParametersInterface;

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
     * @param DatagridInterface $datagrid
     * @param FieldDescriptionInterface $field
     * @return void
     */
    public function addSorter(DatagridInterface $datagrid, FieldDescriptionInterface $field);

    /**
     * @param ProxyQueryInterface $query
     * @param FieldDescriptionCollection $fieldCollection
     * @param ParametersInterface $parameters
     * @return DatagridInterface
     */
    public function getBaseDatagrid(
        ProxyQueryInterface $query,
        FieldDescriptionCollection $fieldCollection,
        ParametersInterface $parameters = null
    );
}
