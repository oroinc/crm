<?php

namespace OroCRM\Bundle\ContactBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

use Oro\Bundle\DataAuditBundle\Metadata\Annotation as Oro;

use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Configurable;

/**
 * @ORM\Entity
 * @ORM\Table(name="orocrm_contact_group")
 * @Oro\Loggable
 * @Configurable(
 *  defaultValues={
 *      "entity"={"label"="Contact Group", "plural_label"="Contact Groups"},
 *      "acl"={"owner_type"="USER"}
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
     */
    protected $owningUser;

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
        return $this->owningUser;
    }

    /**
     * @param User $owningUser
     * @return Group
     */
    public function setOwner(User $owningUser)
    {
        $this->owningUser = $owningUser;

        return $this;
    }
}
