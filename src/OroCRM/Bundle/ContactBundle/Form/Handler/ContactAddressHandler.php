<?php

namespace OroCRM\Bundle\ContactBundle\Form\Handler;

use Oro\Bundle\AddressBundle\Entity\AbstractAddress;
use Oro\Bundle\AddressBundle\Form\Handler\AddressHandler;
use OroCRM\Bundle\ContactBundle\Entity\ContactAddress;

class ContactAddressHandler extends AddressHandler
{
    /**
     * "Success" form handler
     *
     * @param AbstractAddress $address
     * @throws \InvalidArgumentException When argument is not ContactAddress
     */
    protected function onSuccess(AbstractAddress $address)
    {
        if (!$address instanceof ContactAddress) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Argument expect to be an instance of %s, %s given',
                    'OroCRM\Bundle\ContactBundle\Entity\ContactAddress',
                    get_class($address)
                )
            );
        }

        $contact = $address->getOwner();

        if ($address->isPrimary()) {
            $contact->setPrimaryAddress($address);
        }

        foreach ($address->getTypes() as $type) {
            $contact->setAddressType($address, $type);
        }

        $contact->getPrimaryAddress();

        parent::onSuccess($address);
    }
}
