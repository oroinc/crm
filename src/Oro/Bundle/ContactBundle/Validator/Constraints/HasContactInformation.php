<?php

namespace Oro\Bundle\ContactBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class HasContactInformation extends Constraint
{
    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'oro_contact.has_contact_information';
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
