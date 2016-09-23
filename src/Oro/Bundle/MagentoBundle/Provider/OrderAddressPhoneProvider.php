<?php

namespace Oro\Bundle\MagentoBundle\Provider;

use Oro\Bundle\AddressBundle\Provider\PhoneProviderInterface;
use Oro\Bundle\MagentoBundle\Entity\OrderAddress;

class OrderAddressPhoneProvider implements PhoneProviderInterface
{
    /**
     * Gets a phone number of the given OrderAddress object
     *
     * @param OrderAddress $object
     *
     * @return string|null
     */
    public function getPhoneNumber($object)
    {
        return $object->getPhone();
    }

    /**
     * Gets a list of all phone numbers available for the given OrderAddress object
     *
     * @param OrderAddress $object
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
