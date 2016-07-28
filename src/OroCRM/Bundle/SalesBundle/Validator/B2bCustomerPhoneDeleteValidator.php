<?php

namespace OroCRM\Bundle\SalesBundle\Validator;

use OroCRM\Bundle\SalesBundle\Entity\B2bCustomerPhone;

class B2bCustomerPhoneDeleteValidator
{
    /**
     * {@inheritdoc}
     *
     * @param B2bCustomerPhone $value
     */
    public function validate(B2bCustomerPhone $value)
    {
        return $value->isPrimary() && $value->getOwner()->getPhones()->count() === 1;
    }
}
