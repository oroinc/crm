<?php

namespace Oro\Bundle\ContactBundle\Validator\Constraints;

use Oro\Bundle\ContactBundle\Entity\Contact;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Contracts\Translation\TranslatorInterface;

class HasContactInformationValidator extends ConstraintValidator
{
    /** @var TranslatorInterface */
    protected $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$value) {
            return;
        }

        if (!$value instanceof Contact) {
            throw new \InvalidArgumentException(sprintf(
                'Validator expects $value to be instance of "%s"',
                'Oro\Bundle\ContactBundle\Entity\Contact'
            ));
        }

        if ($value->getFirstName() ||
            $value->getLastName() ||
            $value->getEmails()->count() > 0 ||
            $value->getPhones()->count() > 0) {
            return;
        }

        $this->context->addViolation(
            $this->translator->trans('oro.contact.validators.contact.has_information', [], 'validators')
        );
    }
}
