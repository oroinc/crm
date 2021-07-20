<?php

namespace Oro\Bundle\ContactBundle\Formatter;

use Oro\Bundle\ContactBundle\Entity\Contact;
use Oro\Bundle\LocaleBundle\Formatter\NameFormatter;

class ContactNameFormatter
{
    /** @var NameFormatter */
    protected $nameFormatter;

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

        return (string) ($contact->getPrimaryEmail() ?: $contact->getPrimaryPhone());
    }
}
