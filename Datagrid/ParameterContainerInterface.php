<?php

namespace Oro\Bundle\GridBundle\Datagrid;

interface ParameterContainerInterface
{
    /**
     * @return array
     */
    public function getFilterParameters();

    /**
     * @return array
     */
    public function getSorterParameters();

    /**
     * @return array
     */
    public function getPagerParameters();
}
