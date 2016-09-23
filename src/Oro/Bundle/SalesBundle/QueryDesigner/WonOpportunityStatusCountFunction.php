<?php

namespace Oro\Bundle\SalesBundle\QueryDesigner;

class WonOpportunityStatusCountFunction extends AbstractOpportunityStatusCountFunction
{
    /**
     * @return string
     */
    protected function getStatus()
    {
        return 'won';
    }
}
