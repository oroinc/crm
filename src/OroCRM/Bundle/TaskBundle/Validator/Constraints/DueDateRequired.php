<?php

namespace OroCRM\Bundle\TaskBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class DueDateRequired extends Constraint
{
    public $message = 'Due date must be set for {{ field }}';

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'orocrm_task.due_date_required_validator';
    }
}
