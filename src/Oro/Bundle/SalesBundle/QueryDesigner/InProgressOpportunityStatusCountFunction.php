<?php

namespace Oro\Bundle\SalesBundle\QueryDesigner;

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
