<?php

namespace OroCRM\Bundle\ContactBundle\Form\Type;

use Oro\Bundle\AddressBundle\Form\Type\AbstractTypedAddressType;

class ContactAddressType extends AbstractTypedAddressType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'orocrm_contact_address';
    }
}
