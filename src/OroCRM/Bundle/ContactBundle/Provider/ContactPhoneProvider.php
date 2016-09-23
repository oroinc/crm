<?php

namespace Oro\Bundle\ContactBundle\Provider;

use Oro\Bundle\AddressBundle\Provider\PhoneProviderInterface;
use Oro\Bundle\ContactBundle\Entity\Contact;

class ContactPhoneProvider implements PhoneProviderInterface
{
    /**
     * Gets a phone number of the given Contact object
     *
     * @param Contact $object
     *
     * @return string|null
     */
    public function getPhoneNumber($object)
    {
        $primaryPhone = $object->getPrimaryPhone();

        return $primaryPhone ? $primaryPhone->getPhone() : null;
    }

    /**
     * Gets a list of all phone numbers available for the given Contact object
     *
     * @param Contact $object
     *
     * @return array of [phone number, phone owner]
     */
    public function getPhoneNumbers($object)
    {
        $result = [];

        foreach ($object->getPhones() as $phone) {
            $result[] = [$phone->getPhone(), $object];
        }

        return $result;
    }
}
