<?php

namespace Oro\Bundle\ContactBundle\Validator;

use Oro\Bundle\ContactBundle\Entity\ContactPhone;

class ContactPhoneDeleteValidator
{
    /**
     * {@inheritdoc}
     *
     * @param ContactPhone $value
     */
    public function validate(ContactPhone $value)
    {
        return $value->isPrimary() && $value->getOwner()->getPhones()->count() === 1;
    }
}
