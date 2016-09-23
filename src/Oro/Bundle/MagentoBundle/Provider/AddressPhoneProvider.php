<?php

namespace Oro\Bundle\MagentoBundle\Provider;

use Oro\Bundle\AddressBundle\Provider\PhoneProviderInterface;
use Oro\Bundle\MagentoBundle\Entity\Address;

class AddressPhoneProvider implements PhoneProviderInterface
{
    /**
     * Gets a phone number of the given Address object
     *
     * @param Address $object
     *
     * @return string|null
     */
    public function getPhoneNumber($object)
    {
        $phone = $object->getPhone();
        if (empty($phone) && $object->getContactPhone()) {
            $phone = $object->getContactPhone()->getPhone();
        }

        return !empty($phone) ? $phone : null;
    }

    /**
     * Gets a list of all phone numbers available for the given Address object
     *
     * @param Address $object
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
        if ($object->getContactPhone() && $object->getContactPhone()->getPhone() !== $phone) {
            $result[] = [$object->getContactPhone()->getPhone(), $object->getContactPhone()->getOwner()];
        }

        return $result;
    }
}
