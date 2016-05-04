<?php

namespace OroCRM\Bundle\SalesBundle\QueryDesigner;

class SolutionDevelopmentOpportunityStatusCountFunction extends AbstractOpportunityStatusCountFunction
{
    /**
     * @return string
     */
    protected function getStatus()
    {
        return 'solution_development';
    }
}
