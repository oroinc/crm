<?php

namespace Oro\Bundle\ContactBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * This constraint is used to check whether at least one of the fields first name, last name,
 * emails or phones is defined for Contact entity.
 */
class HasContactInformation extends Constraint
{
    public string $message = 'oro.contact.validators.contact.has_information';

    /**
     * {@inheritDoc}
     */
    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
