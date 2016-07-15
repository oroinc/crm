<?php

namespace OroCRM\Bundle\SalesBundle\Model;

use Oro\Bundle\AddressBundle\Entity\AbstractPhone;

class ExtendLeadPhone extends AbstractPhone
{
    /**
     * Constructor
     *
     * The real implementation of this method is auto generated.
     *
     * IMPORTANT: If the derived class has own constructor it must call parent constructor.
     *
     * @param string|null $phone
     */
    public function __construct($phone = null)
    {
        parent::__construct($phone);
    }
}
