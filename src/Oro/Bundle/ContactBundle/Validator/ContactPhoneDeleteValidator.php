<?php

namespace Oro\Bundle\ContactBundle\Validator;

use Symfony\Component\Translation\TranslatorInterface;

use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Entity\ContactPhone;

class ContactPhoneDeleteValidator
{
    /** @var TranslatorInterface  */
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
     *
     * @param ContactPhone $value
     */
    public function validate(ContactPhone $value)
    {
        $this->checkIsPrimary($value);
        $this->checkContactHasInformation($value->getOwner());

        return true;
    }

    /**
     * @param ContactPhone $value
     * @throws \Exception
     */
    protected function checkIsPrimary(ContactPhone $value)
    {
        if (!$value->isPrimary() || $value->getOwner()->getPhones()->count() === 1) {
            return;
        }

        throw new \Exception(
            $this->translator->trans('oro.contact.validators.phones.delete.more_one', [], 'validators'),
            400
        );
    }

    /**
     * @param Contact $contact
     * @throws \Exception
     */
    protected function checkContactHasInformation(Contact $contact)
    {
        if ($contact->getFirstName() ||
            $contact->getLastName() ||
            $contact->getEmails()->count() > 0 ||
            $contact->getPhones()->count() > 1) {
            return;
        }

        throw new \Exception(
            $this->translator->trans('oro.contact.validators.contact.has_information', [], 'validators'),
            400
        );
    }
}
