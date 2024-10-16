<?php

namespace Oro\Bundle\SalesBundle\QueryDesigner;

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
