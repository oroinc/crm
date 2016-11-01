<?php

namespace Oro\Bundle\SalesBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class Customer extends Constraint
{
    /** @var string */
    public $message = 'There should not be more than one customer.';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'oro_sales.validator.customer';
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return static::CLASS_CONSTRAINT;
    }
}
