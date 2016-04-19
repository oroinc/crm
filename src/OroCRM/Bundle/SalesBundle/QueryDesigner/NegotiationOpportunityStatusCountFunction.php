<?php

namespace OroCRM\Bundle\SalesBundle\QueryDesigner;

class NegotiationOpportunityStatusCountFunction extends AbstractOpportunityStatusCountFunction
{
    /**
     * @return string
     */
    protected function getStatus()
    {
        return 'negotiation';
    }
}
