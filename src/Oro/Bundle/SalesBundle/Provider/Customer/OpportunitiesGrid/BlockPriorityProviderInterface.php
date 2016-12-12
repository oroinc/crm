<?php

namespace Oro\Bundle\SalesBundle\Provider\Customer\OpportunitiesGrid;

interface BlockPriorityProviderInterface
{
    /**
     * @param string $targetClass
     *
     * @return int|null
     */
    public function getPriority($targetClass);
}
