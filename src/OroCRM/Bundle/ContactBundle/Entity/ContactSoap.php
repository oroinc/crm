<?php

namespace OroCRM\Bundle\ContactBundle\Entity;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

class ContactSoap extends Contact
{
    /**
     * @Soap\ComplexType("Oro\Bundle\SoapBundle\Entity\FlexibleAttribute[]", nillable=true)
     */
    protected $attributes;
}
