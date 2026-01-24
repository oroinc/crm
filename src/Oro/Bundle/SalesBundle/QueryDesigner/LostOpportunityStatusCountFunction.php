<?php

namespace Oro\Bundle\SalesBundle\QueryDesigner;

/**
 * Implements a query designer function for counting opportunities with lost status.
 */
class LostOpportunityStatusCountFunction extends AbstractOpportunityStatusCountFunction
{
    /**
     * @return string
     */
    #[\Override]
    protected function getStatus()
    {
        return 'lost';
    }
}
