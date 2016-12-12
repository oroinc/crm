<?php

namespace Oro\Bundle\SalesBundle\Provider\Customer\OpportunitiesGrid;

class DefaultBlockPriorityProvider implements BlockPriorityProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getPriority($targetClass)
    {
        // below activity block which have 1000
        return 1010;
    }
}
