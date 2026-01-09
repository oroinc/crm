<?php

namespace Oro\Bundle\ChannelBundle\Validator;

use Oro\Bundle\ChannelBundle\Entity\Channel;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Defines a validation constraint for channel customer identity configuration.
 */
class ChannelCustomerIdentityConstraintValidator extends ConstraintValidator
{
    #[\Override]
    public function validate($value, Constraint $constraint)
    {
        if (!($value instanceof Channel)) {
            throw new UnexpectedTypeException($value, 'Channel');
        }

        $this->validateCustomerIdentity($value);
    }

    protected function validateCustomerIdentity(Channel $channel)
    {
        $errorLabel = 'oro.channel.form.customer_identity_selected_not_correctly.label';
        $fieldName  = 'customerIdentity';
        $entities   = $channel->getEntities();

        if (!in_array($channel->getCustomerIdentity(), $entities)) {
            $this->context->buildViolation($errorLabel)
                ->atPath($fieldName)
                ->addViolation();
        }
    }
}
