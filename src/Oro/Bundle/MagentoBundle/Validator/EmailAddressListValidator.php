<?php

namespace Oro\Bundle\MagentoBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\EmailValidator;
use Symfony\Component\Validator\ConstraintValidator;

class EmailAddressListValidator extends ConstraintValidator
{
    /**
     * @param array      $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (!is_array($value)) {
            return;
        }

        $emailValidator = new EmailValidator();
        $emailValidator->initialize($this->context);

        foreach ($value as $emailAddress) {
            $emailValidator->validate($emailAddress, $constraint);
        }
    }
}
