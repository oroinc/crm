<?php

namespace OroCRM\Bundle\ChannelBundle\Validator;

use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

use OroCRM\Bundle\ChannelBundle\Entity\Channel;

class ChannelValidator extends ConstraintValidator
{
    /** @var Constraint */
    protected $constraint;

    /** @var TranslatorInterface */
    protected $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!($value instanceof Channel)) {
            throw new UnexpectedTypeException($value, 'Channel');
        }

        $this->constraint = $constraint;

        $this->validateCustomerIdentity($value);
    }

    /**
     * @param Channel $channel
     */
    protected function validateCustomerIdentity(Channel $channel)
    {
        $errorLabel = 'orocrm.channel.form.customer_identity_selected_not_correctly.label';
        $entities   = $channel->getEntities();

        if (!in_array($channel->getCustomerIdentity(), $entities)) {
            $this->addErrorMessage($errorLabel);
        }
    }

    /**
     * @param string $errorLabel
     */
    protected function addErrorMessage($errorLabel)
    {
        $this->context->addViolation(
            $this->translator->trans($errorLabel)
        );
    }
}
