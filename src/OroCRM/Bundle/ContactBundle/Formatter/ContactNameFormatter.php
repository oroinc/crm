<?php

namespace OroCRM\Bundle\ContactBundle\Formatter;

use Oro\Bundle\LocaleBundle\Formatter\NameFormatter;

use OroCRM\Bundle\ContactBundle\Entity\Contact;

class ContactNameFormatter
{
    /** @var NameFormatter */
    protected $nameFormatter;

    /**
     * @param NameFormatter $nameFormatter
     */
    public function __construct(NameFormatter $nameFormatter)
    {
        $this->nameFormatter = $nameFormatter;
    }

    /**
     * @param Contact $contact
     * @param string|null $locale
     *
     * @return string
     */
    public function format(Contact $contact, $locale = null)
    {
        if ($name = $this->nameFormatter->format($contact, $locale)) {
            return $name;
        }

        return (string) ($contact->getPrimaryPhone() ?: $contact->getPrimaryEmail());
    }
}
