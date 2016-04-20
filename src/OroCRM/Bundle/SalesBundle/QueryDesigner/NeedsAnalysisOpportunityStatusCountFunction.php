<?php

namespace OroCRM\Bundle\SalesBundle\QueryDesigner;

class NeedsAnalysisOpportunityStatusCountFunction extends AbstractOpportunityStatusCountFunction
{
    /**
     * @return string
     */
    protected function getStatus()
    {
        return 'needs_analysis';
    }
}
