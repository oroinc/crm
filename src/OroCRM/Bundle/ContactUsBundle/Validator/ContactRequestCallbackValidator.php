<?php

namespace OroCRM\Bundle\ContactUsBundle\Validator;

use Symfony\Component\Validator\Context\ExecutionContextInterface;

use OroCRM\Bundle\ContactUsBundle\Entity\ContactRequest;

class ContactRequestCallbackValidator
{
    /**
     * Validates contact method
     *
     * @param ContactRequest            $object
     * @param ExecutionContextInterface $context
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
            $context->addViolationAt('emailAddress', 'This value should not be blank.');
        }
        if ($phoneError) {
            $context->addViolationAt('phone', 'This value should not be blank.');
        }
    }
}
