<?php

namespace Oro\Bundle\SalesBundle\Validator;

use Oro\Bundle\SalesBundle\Entity\B2bCustomerEmail;

class B2bCustomerEmailDeleteValidator
{
    /**
     * {@inheritdoc}
     *
     * @param B2bCustomerEmail $value
     */
    public function validate(B2bCustomerEmail $value)
    {
        return $value->isPrimary() && $value->getOwner()->getEmails()->count() === 1;
    }
}
