<?php

namespace OroCRM\Bundle\TaskBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

use OroCRM\Bundle\TaskBundle\Validator\Constraints\DueDateRequired;
use OroCRM\Bundle\TaskBundle\Entity\Task;

class DueDateRequiredValidator extends ConstraintValidator
{
    /**
     * @param Task                       $value
     * @param Constraint|DueDateRequired $constraint
     * @throws \InvalidArgumentException
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$value instanceof Task) {
            throw new \InvalidArgumentException(
                sprintf(
                    'OroCRM\Bundle\TaskBundle\Entity\Task supported only, %s given',
                    is_object($value) ? get_class($value) : gettype($value)
                )
            );
        }

        if (count($value->getReminders()) > 0 && !$value->getDueDate()) {
            $this->context->addViolationAt('dueDate', $constraint->message, ['{{ field }}'  => 'reminders']);
        }
    }
}
