<?php
namespace Oro\Bundle\AddressBundle\Model\AttributeType;

use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttributeType;

class AddressType extends AbstractAttributeType
{
    const BACKEND_TYPE_ADDRESS = 'address';

    /**
     * constructor
     */
    public function __construct()
    {
        $this->name        = 'Address';
        $this->backendType = self::BACKEND_TYPE_ADDRESS;
        $this->formType    = 'oro_address';
    }
}
