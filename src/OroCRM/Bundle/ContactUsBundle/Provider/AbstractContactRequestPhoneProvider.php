<?php

namespace Oro\Bundle\ContactUsBundle\Provider;

use Oro\Bundle\AddressBundle\Provider\PhoneProviderInterface;
use Oro\Bundle\ContactUsBundle\Entity\AbstractContactRequest;

class AbstractContactRequestPhoneProvider implements PhoneProviderInterface
{
    /**
     * Gets a phone number of the given AbstractContactRequest object
     *
     * @param AbstractContactRequest $object
     *
     * @return string|null
     */
    public function getPhoneNumber($object)
    {
        return $object->getPhone();
    }

    /**
     * Gets a list of all phone numbers available for the given AbstractContactRequest object
     *
     * @param AbstractContactRequest $object
     *
     * @return array of [phone number, phone owner]
     */
    public function getPhoneNumbers($object)
    {
        $result = [];

        $phone = $object->getPhone();
        if (!empty($phone)) {
            $result[] = [$phone, $object];
        }

        return $result;
    }
}
