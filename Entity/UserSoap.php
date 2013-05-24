<?php

namespace Oro\Bundle\UserBundle\Entity;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

use JMS\Serializer\Annotation\Exclude;

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
     * @Soap\ComplexType("string")
     */
    protected $firstName;

    /**
     * @Soap\ComplexType("string")
     */
    protected $lastName;

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
     * @Exclude
     */
    protected $roles;

    /**
     * @Soap\ComplexType("int[]")
     */
    protected $rolesCollection;

    /**
     * @Soap\ComplexType("int[]", nillable=true)
     */
    protected $groups;

    /**
     * @Soap\ComplexType("Oro\Bundle\SoapBundle\Entity\FlexibleAttribute[]", nillable=true)
     */
    protected $values;

    public function setRolesCollection($collection)
    {
        $this->rolesCollection = $collection;
    }

    public function getRolesCollection()
    {
        return $this->rolesCollection;
    }
}
