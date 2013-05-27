<?php

namespace OroCRM\Bundle\AccountBundle\Entity;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;
use JMS\Serializer\Annotation\Exclude;

class AccountSoap extends Account
{
    /**
     * @Exclude
     */
    protected $values;

    /**
     * @Soap\ComplexType("Oro\Bundle\SoapBundle\Entity\FlexibleAttribute[]", nillable=true)
     */
    protected $attributes;
}
