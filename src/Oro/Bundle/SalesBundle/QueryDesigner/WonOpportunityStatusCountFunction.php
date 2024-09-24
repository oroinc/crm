<?php

namespace Oro\Bundle\SalesBundle\QueryDesigner;

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
