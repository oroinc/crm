<?php

namespace Oro\Bundle\SalesBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

use Oro\Bundle\SalesBundle\Manager\CustomerManager;

class CustomerValidator extends ConstraintValidator
{
    /** @var CustomerManager */
    protected $customerManager;

    /**
     * @param CustomerManager $customerManager
     */
    public function __construct(CustomerManager $customerManager)
    {
        $this->customerManager = $customerManager;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$value || !$this->customerManager->hasMoreCustomers($value)) {
            return;
        }

        $this->context->addViolation($constraint->message);
    }
}
