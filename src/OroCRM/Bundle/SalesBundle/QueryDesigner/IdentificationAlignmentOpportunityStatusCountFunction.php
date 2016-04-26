<?php

namespace OroCRM\Bundle\SalesBundle\QueryDesigner;

class IdentificationAlignmentOpportunityStatusCountFunction extends AbstractOpportunityStatusCountFunction
{
    /**
     * @return string
     */
    protected function getStatus()
    {
        return 'identification_alignment';
    }
}
