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
        if (!$value) {
            return;
        }

        if ($this->customerManager->hasMoreCustomers($value)) {
            $this->context->addViolation($constraint->message);
        } elseif ($constraint->required && !$this->customerManager->getCustomer($value)) {
            $this->context->addViolation($constraint->requiredMessage);
        }
    }
}
