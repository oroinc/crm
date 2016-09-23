<?php

namespace Oro\Bundle\ChannelBundle\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

use Oro\Bundle\ChannelBundle\Entity\Channel;

class ChannelCustomerIdentityConstraintValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!($value instanceof Channel)) {
            throw new UnexpectedTypeException($value, 'Channel');
        }

        $this->validateCustomerIdentity($value);
    }

    /**
     * @param Channel $channel
     */
    protected function validateCustomerIdentity(Channel $channel)
    {
        $errorLabel = 'oro.channel.form.customer_identity_selected_not_correctly.label';
        $fieldName  = 'customerIdentity';
        $entities   = $channel->getEntities();

        if (!in_array($channel->getCustomerIdentity(), $entities)) {
            $this->context->addViolationAt($fieldName, $errorLabel);
        }
    }
}
