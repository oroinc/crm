<?php

namespace OroCRM\Bundle\ContactBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

use Oro\Bundle\DataAuditBundle\Metadata\Annotation as Oro;

use Oro\Bundle\UserBundle\Entity\User;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;

/**
 * @ORM\Entity
 * @ORM\Table(name="orocrm_contact_group")
 * @Oro\Loggable
 * @Config(
 *  defaultValues={
 *      "ownership"={
 *          "owner_type"="USER",
 *          "owner_field_name"="owner",
 *          "owner_column_name"="user_owner_id"
 *      },
 *      "security"={
 *          "type"="ACL",
 *          "group_name"=""
 *      }
 *  }
 * )
 */
class Group
{
    /**
     * @ORM\Id
     * @ORM\Column(type="smallint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Soap\ComplexType("int", nillable=true)
     */
    protected $id;

    /**
     * @ORM\Column(type="string", unique=true, length=30, nullable=false)
     * @Soap\ComplexType("string")
     * @Oro\Versioned
     */
    protected $label;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_owner_id", referencedColumnName="id", onDelete="SET NULL")
     * @Soap\ComplexType("string", nillable=true)
     */
    protected $owner;

    /**
     * @param string|null $label [optional] Group name
     */
    public function __construct($label = null)
    {
        $this->label = $label;
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
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param  string $name
     * @return Group
     */
    public function setLabel($name)
    {
        $this->label = $name;

        return $this;
    }

    public function __toString()
    {
        return (string)$this->getLabel();
    }

    /**
     * @return User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param User $owningUser
     * @return Group
     */
    public function setOwner($owningUser)
    {
        $this->owner = $owningUser;

        return $this;
    }
}
