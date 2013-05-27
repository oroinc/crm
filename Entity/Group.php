<?php

namespace OroCRM\Bundle\ContactBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;

use Doctrine\ORM\Mapping as ORM;

use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\Exclude;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

/**
 * @ORM\Entity
 * @ORM\Table(name="orocrm_contact_group")
 * @Gedmo\Loggable(logEntryClass="Oro\Bundle\DataAuditBundle\Entity\Audit")
 */
class Group
{
    /**
     * @ORM\Id
     * @ORM\Column(type="smallint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Soap\ComplexType("int", nillable=true)
     * @Type("integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", unique=true, length=30, nullable=false)
     * @Soap\ComplexType("string")
     * @Type("string")
     * @Gedmo\Versioned
     */
    protected $name;

    /**
     * @param string $name [optional] Group name
     */
    public function __construct($name = '')
    {
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param  string $name
     * @return Group
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function __toString()
    {
        return (string)$this->getName();
    }
}
