<?php

namespace Oro\Bundle\ChannelBundle\Validator;

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

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'oro_channel.validator.channel_integration';
    }
}
