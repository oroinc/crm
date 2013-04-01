<?php

namespace Oro\Bundle\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\Exclude;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

/**
 * @ORM\Entity
 * @ORM\Table(name="oro_access_group")
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
     */
    protected $name;

    /**
     * @ORM\ManyToMany(targetEntity="Role")
     * @ORM\JoinTable(name="oro_user_access_group_role",
     *      joinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     * @Soap\ComplexType("int[]")
     * @Exclude
     */
    protected $roles;

    /**
     * @param string $name [optional] Group name
     */
    public function __construct($name = '')
    {
        $this->name  = $name;
        $this->roles = new ArrayCollection();
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

    public function getRoleLabelsAsString()
    {
        $labels = array();
        foreach ($this->getRoles() as $role) {
            $labels[] = $role->getLabel();
        }

        return implode(', ', $labels);
    }

    /**
     * Returns the group roles
     *
     * @return ArrayCollection The roles
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * Pass a string, get the desired Role object or null
     *
     * @param  string    $role Role name
     * @return Role|null
     */
    public function getRole($role)
    {
        foreach ($this->getRoles() as $item) {
            if ($role == $item->getRole()) {
                return $item;
            }
        }

        return null;
    }

    /**
     * @param  string  $role
     * @return boolean
     */
    public function hasRole($role)
    {
        return $this->getRole($role) ? true : false;
    }

    /**
     * Adds a Role to the ArrayCollection
     *
     * @param  Role  $role
     * @return Group
     */
    public function addRole(Role $role)
    {
        if (!$this->hasRole($role->getRole())) {
            $this->roles->add($role);
        }

        return $this;
    }

    /**
     * Pass a string, remove the Role object from collection
     *
     * @param  string $role
     * @return Group
     */
    public function removeRole($role)
    {
        $item = $this->getRole($role);

        if ($item) {
            $this->roles->removeElement($item);
        }

        return $this;
    }

    /**
     * Set new Roles collection
     *
     * @param  array|Collection $roles
     * @return Group
     */
    public function setRoles($roles)
    {
        if ($roles instanceof Collection) {
            $this->roles->clear();

            foreach ($roles as $role) {
                $this->addRole($role);
            }
        } else {
            $this->roles = $roles;
        }

        return $this;
    }
}
