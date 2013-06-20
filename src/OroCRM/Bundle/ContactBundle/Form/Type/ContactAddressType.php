<?php

namespace OroCRM\Bundle\ContactBundle\Form\Type;

use Oro\Bundle\AddressBundle\Form\Type\AddressTypedType;

class ContactAddressType extends AddressTypedType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'orocrm_contact_address';
    }
}
