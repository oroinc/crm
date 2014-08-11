<?php

namespace OroCRM\Bundle\ChannelBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class ChannelCustomerIdentityConstraint extends Constraint
{
    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'orocrm_channle.channel_validator.customer_identity';
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
