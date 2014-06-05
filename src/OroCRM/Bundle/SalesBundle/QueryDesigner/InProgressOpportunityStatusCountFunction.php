<?php

namespace OroCRM\Bundle\SalesBundle\QueryDesigner;

class InProgressOpportunityStatusCountFunction extends AbstractOpportunityStatusCountFunction
{
    /**
     * @return string
     */
    protected function getStatus()
    {
        return 'in_progress';
    }
}
