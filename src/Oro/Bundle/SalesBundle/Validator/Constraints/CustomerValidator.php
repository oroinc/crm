<?php

namespace Oro\Bundle\SalesBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

use Oro\Bundle\SalesBundle\Manager\OpportunityCustomerManager;

class CustomerValidator extends ConstraintValidator
{
    /** @var OpportunityCustomerManager */
    protected $opportunityCustomerManager;

    /**
     * @param OpportunityCustomerManager $opportunityCustomerManager
     */
    public function __construct(OpportunityCustomerManager $opportunityCustomerManager)
    {
        $this->opportunityCustomerManager = $opportunityCustomerManager;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$value || !$this->opportunityCustomerManager->hasMoreCustomers($value)) {
            return;
        }

        $this->context->addViolation($constraint->message);
    }
}
