<?php

namespace Oro\Bundle\ChannelBundle\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * Defines a validation constraint for channel customer identity configuration.
 */
class ChannelCustomerIdentityConstraint extends Constraint
{
    #[\Override]
    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
