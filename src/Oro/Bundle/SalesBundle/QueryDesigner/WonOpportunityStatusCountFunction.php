<?php

namespace Oro\Bundle\SalesBundle\QueryDesigner;

/**
 * Implements a query designer function for counting opportunities with won status.
 */
class WonOpportunityStatusCountFunction extends AbstractOpportunityStatusCountFunction
{
    /**
     * @return string
     */
    #[\Override]
    protected function getStatus()
    {
        return 'won';
    }
}
