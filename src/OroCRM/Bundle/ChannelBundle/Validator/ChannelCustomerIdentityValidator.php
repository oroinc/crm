<?php

namespace OroCRM\Bundle\ChannelBundle\Validator;

use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

use OroCRM\Bundle\ChannelBundle\Entity\Channel;

class ChannelCustomerIdentityValidator extends ConstraintValidator
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
        $fieldName  = 'customerIdentity';
        $entities   = $channel->getEntities();

        if (in_array($channel->getCustomerIdentity(), $entities)) {
            $this->addErrorMessage($fieldName, $errorLabel);
        }
    }

    /**
     * @param string $fieldName
     * @param string $errorLabel
     */
    protected function addErrorMessage($fieldName, $errorLabel)
    {
        $this->context->addViolationAt(
            $fieldName,
            $this->translator->trans($errorLabel)
        );
    }
}
