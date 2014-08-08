<?php

namespace OroCRM\Bundle\ChannelBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class ChannelConstraint extends Constraint
{
    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'orocrm_channle.channel_validator';
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
