<?php

namespace Oro\Bundle\SalesBundle\QueryDesigner;

/**
 * Implements a query designer function for counting opportunities with in-progress status.
 */
class InProgressOpportunityStatusCountFunction extends AbstractOpportunityStatusCountFunction
{
    /**
     * @return string
     */
    #[\Override]
    protected function getStatus()
    {
        return 'in_progress';
    }
}
