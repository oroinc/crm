<?php

namespace Oro\Bundle\ContactBundle\Validator;

use Oro\Bundle\ContactBundle\Entity\ContactEmail;

class ContactEmailDeleteValidator
{
    /**
     * {@inheritdoc}
     *
     * @param ContactEmail $value
     */
    public function validate(ContactEmail $value)
    {
        return $value->isPrimary() && $value->getOwner()->getEmails()->count() === 1;
    }
}
