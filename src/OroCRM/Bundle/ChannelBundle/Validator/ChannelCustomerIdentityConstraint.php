<?php

namespace OroCRM\Bundle\ChannelBundle\Validator;

use Symfony\Component\Validator\Constraint;

class ChannelCustomerIdentityConstraint extends Constraint
{
    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
