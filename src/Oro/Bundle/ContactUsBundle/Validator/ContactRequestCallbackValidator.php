<?php

namespace Oro\Bundle\ContactUsBundle\Validator;

use Oro\Bundle\ContactUsBundle\Entity\ContactRequest;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ContactRequestCallbackValidator
{
    /**
     * Validates contact method
     */
    public static function validate(ContactRequest $object, ExecutionContextInterface $context)
    {
        $emailError = $phoneError = false;

        switch ($object->getPreferredContactMethod()) {
            case ContactRequest::CONTACT_METHOD_PHONE:
                $phoneError = !$object->getPhone();
                break;
            case ContactRequest::CONTACT_METHOD_EMAIL:
                $emailError = !$object->getEmailAddress();
                break;
            case ContactRequest::CONTACT_METHOD_BOTH:
            default:
                $phoneError = !$object->getPhone();
                $emailError = !$object->getEmailAddress();
        }

        if ($emailError) {
            $context->buildViolation('This value should not be blank.')
                ->atPath('emailAddress')
                ->addViolation();
        }
        if ($phoneError) {
            $context->buildViolation('This value should not be blank.')
                ->atPath('phone')
                ->addViolation();
        }
    }
}
