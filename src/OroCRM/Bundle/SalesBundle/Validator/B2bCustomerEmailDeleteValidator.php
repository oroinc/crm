<?php

namespace OroCRM\Bundle\SalesBundle\Validator;

use OroCRM\Bundle\SalesBundle\Entity\B2bCustomerEmail;

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
