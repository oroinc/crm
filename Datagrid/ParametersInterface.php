<?php

namespace Oro\Bundle\GridBundle\Datagrid;

interface ParametersInterface
{
    /**
     * Get list of datagrid parameters (filters, sorters etc.)
     *
     * @param string|null $datagridId
     * @return ParameterContainerInterface|null
     */
    public function getParameters($datagridId = null);
}
