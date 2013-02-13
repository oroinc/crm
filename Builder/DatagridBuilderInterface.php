<?php

namespace Oro\Bundle\GridBundle\Builder;

use Oro\Bundle\GridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;

interface DatagridBuilderInterface
{
    /**
     * @param DatagridInterface $datagrid
     * @param string $type
     * @param FieldDescriptionInterface $fieldDescription
     * @return void
     */
    public function addFilter(DatagridInterface $datagrid, $type = null, FieldDescriptionInterface $fieldDescription);

    /**
     * @param array $values
     * @return DatagridInterface
     */
    public function getBaseDatagrid(array $values = array());
}
