<?php

namespace OroCRM\Bundle\ContactBundle\Form\Handler;

use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\ContactBundle\Entity\ContactAddress;
use Oro\Bundle\AddressBundle\Form\Handler\AddressHandler;

class ContactAddressHandler extends AddressHandler
{
    /**
     * "Success" form handler
     *
     * @param ContactAddress $address
     */
    protected function onSuccess(ContactAddress $address)
    {
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
