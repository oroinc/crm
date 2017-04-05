<?php

namespace OroCRM\Bundle\MagentoBundle\Manager\CustomerAddress;

use Symfony\Component\PropertyAccess\PropertyAccess;

use OroCRM\Bundle\ContactBundle\Entity\ContactAddress;
use OroCRM\Bundle\MagentoBundle\Entity\Address;

class ConvertAddressToContactAdress
{
    protected $baseAddressProperties = [
        'label',
        'street',
        'street2',
        'city',
        'postalCode',
        'country',
        'organization',
        'region',
        'regionText',
        'namePrefix',
        'firstName',
        'middleName',
        'lastName',
        'nameSuffix',
        'types',
        'primary'
    ];

    public function __construct()
    {
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * @param Address $customerAddress
     *
     * @return ContactAddress
     */
    public function convert(Address $customerAddress)
    {
        $contactAddress = new ContactAddress();
        foreach ($this->baseAddressProperties as $property) {
            $this->accessor->setValue(
                $contactAddress,
                $property,
                $this->accessor->getValue($customerAddress, $property)
            );
        }

        return $contactAddress;
    }
}
