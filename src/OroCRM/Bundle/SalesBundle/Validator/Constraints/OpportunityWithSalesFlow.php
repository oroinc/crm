<?php

namespace OroCRM\Bundle\SalesBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class OpportunityWithSalesFlow extends Constraint
{
    public $message = 'Opportunity already has related sales flow opportunity';

    /**
     * {@inheritDoc}
     */
    public function validatedBy()
    {
        return 'orocrm_sales_validator_opportunity_with_sales_flow';
    }

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
