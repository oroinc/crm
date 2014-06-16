<?php

namespace OroCRM\Bundle\SalesBundle\QueryDesigner;

class LostOpportunityStatusCountFunction extends AbstractOpportunityStatusCountFunction
{
    /**
     * @return string
     */
    protected function getStatus()
    {
        return 'lost';
    }
}
