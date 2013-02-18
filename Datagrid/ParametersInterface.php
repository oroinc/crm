<?php

namespace Oro\Bundle\GridBundle\Datagrid;

interface ParametersInterface
{
    /**
     * Get parameter name from parameters container
     *
     * @param string $name
     * @return mixed
     */
    public function get($name);
}
