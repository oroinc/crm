<?php

namespace OroCRM\Bundle\SalesBundle\Provider;

use Oro\Bundle\AddressBundle\Provider\PhoneProviderInterface;
use OroCRM\Bundle\SalesBundle\Entity\Lead;

class LeadPhoneProvider implements PhoneProviderInterface
{
    /**
     * Gets a phone number of the given Lead object
     *
     * @param Lead $object
     *
     * @return string|null
     */
    public function getPhoneNumber($object)
    {
        $primaryPhone = $object->getPrimaryPhone();

        return $primaryPhone ? $primaryPhone->getPhone() : null;
    }

    /**
     * Gets a list of all phone numbers available for the given Lead object
     *
     * @param Lead $object
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
