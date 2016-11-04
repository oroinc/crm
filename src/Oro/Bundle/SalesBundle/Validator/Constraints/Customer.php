<?php

namespace Oro\Bundle\SalesBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class Customer extends Constraint
{
    /** @var string */
    public $message = 'There should not be more than one customer.';

    /** @var string */
    public $requiredMessage = 'Customer is required.';

    /** @var bool */
    public $required = false;

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return static::CLASS_CONSTRAINT;
    }
}
