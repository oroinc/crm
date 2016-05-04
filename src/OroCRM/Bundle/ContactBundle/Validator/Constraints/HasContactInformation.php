<?php

namespace OroCRM\Bundle\ContactBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class HasContactInformation extends Constraint
{
    /** @var string */
    public $message = 'At least one of the fields %fields% must be defined.';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'orocrm_contact.has_contact_information';
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return [
            static::CLASS_CONSTRAINT,
        ];
    }
}
