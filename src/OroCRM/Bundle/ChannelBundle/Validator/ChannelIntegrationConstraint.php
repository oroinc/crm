<?php

namespace OroCRM\Bundle\ChannelBundle\Validator;

use Symfony\Component\Validator\Constraint;

class ChannelIntegrationConstraint extends Constraint
{
    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy()
    {
        return 'alias_name';
    }
}
