<?php

namespace Oro\Bundle\ContactBundle\Validator;

use Symfony\Component\Translation\TranslatorInterface;

use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\ContactBundle\Entity\ContactEmail;

class ContactEmailDeleteValidator
{
    /** @var TranslatorInterface  */
    protected $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     *
     * @param ContactEmail $value
     */
    public function validate(ContactEmail $value)
    {
        $this->checkIsPrimary($value);
        $this->checkContactHasInformation($value->getOwner());

        return true;
    }

    /**
     * @param ContactEmail $value
     * @throws \Exception
     */
    protected function checkIsPrimary(ContactEmail $value)
    {
        if (!$value->isPrimary() || $value->getOwner()->getEmails()->count() === 1) {
            return;
        }

        throw new \Exception("oro.contact.email.error.delete.more_one", 500);
    }

    /**
     * @param Contact $contact
     * @throws \Exception
     */
    protected function checkContactHasInformation(Contact $contact)
    {
        if ($contact->getFirstName() ||
            $contact->getLastName() ||
            $contact->getEmails()->count() > 1 ||
            $contact->getPhones()->count() > 0) {
            return;
        }
        $fields = sprintf(
            '%s, %s, %s or %s',
            $this->translator->trans('oro.contact.first_name.label'),
            $this->translator->trans('oro.contact.last_name.label'),
            $this->translator->trans('oro.contact.emails.label'),
            $this->translator->trans('oro.contact.phones.label')
        );
        throw new \Exception(
            $this->translator->trans('oro.contact.validators.contact.has_information', ['%fields%' => $fields]),
            500
        );
    }
}
