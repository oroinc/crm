<?php

namespace Oro\Bundle\ContactBundle\Validator\Constraints;

use Oro\Bundle\ContactBundle\Entity\Contact;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Validates whether at least one of the fields first name, last name,
 * emails or phones is defined for Contact entity.
 */
class HasContactInformationValidator extends ConstraintValidator
{
    /**
     * {@inheritDoc}
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof HasContactInformation) {
            throw new UnexpectedTypeException($constraint, HasContactInformation::class);
        }

        if (null === $value) {
            return;
        }

        if (!$value instanceof Contact) {
            throw new UnexpectedTypeException($value, Contact::class);
        }

        if ($value->getFirstName()
            || $value->getLastName()
            || $value->getEmails()->count() > 0
            || $value->getPhones()->count() > 0
        ) {
            return;
        }

        $this->context->addViolation($constraint->message);
    }
}
