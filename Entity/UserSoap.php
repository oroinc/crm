<?php

namespace Oro\Bundle\UserBundle\Entity;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

class UserSoap extends User
{
    /**
     * @Soap\ComplexType("int", nillable=true)
     */
    protected $id;

    /**
     * @Soap\ComplexType("string")
     */
    protected $username;

    /**
     * @Soap\ComplexType("string")
     */
    protected $email;

    /**
     * @Soap\ComplexType("boolean")
     */
    protected $enabled = true;

    /**
     * @Soap\ComplexType("string", nillable=true)
     */
    protected $plainPassword;

    /**
     * @Soap\ComplexType("dateTime", nillable=true)
     */
    protected $lastLogin;

    /**
     * @Soap\ComplexType("int[]", nillable=true)
     */
    protected $rolesCollection;

    /**
     * @Soap\ComplexType("int[]", nillable=true)
     */
    protected $groups;

    /**
     * @Soap\ComplexType("string[]", nillable=true)
     */
    protected $attributes;
}
