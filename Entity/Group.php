<?php

namespace Oro\Bundle\UserBundle\Entity;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

use FOS\UserBundle\Model\Group as BaseGroup;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * @ORM\Entity
 * @ORM\Table(name="access_group")
 * @UniqueEntity("name")
 */
class Group extends BaseGroup
{
    /**
     * @ORM\Id
     * @ORM\Column(type="smallint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", unique=true, length=30, nullable=false)
     */
    protected $name;

    /**
     * @ORM\ManyToMany(targetEntity="Role")
     * @ORM\JoinTable(name="access_group_role",
     *      joinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id", onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     */
    protected $roles;

    /**
     * @param   string  $name  Group name
     * @param   array   $roles Array of Role objects
     */
    public function __construct($name = '', $roles = array())
    {
        $this->name  = $name;
        $this->roles = new ArrayCollection();

        $this->setRoles($roles);
    }

    /**
     * Returns the group roles
     *
     * @return array The roles
     */
    public function getRoles()
    {
        return $this->roles->toArray();
    }

    /**
     * Returns the true ArrayCollection of Roles.
     *
     * @return ArrayCollection
     */
    public function getRolesCollection()
    {
        return $this->roles;
    }

    /**
     * Pass a string, get the desired Role object or null
     *
     * @param   string  $role Role name
     * @return  Role|null
     */
    public function getRole($role)
    {
        foreach ($this->getRoles() as $item ) {
            if ($role == $item->getRole()) {
                return $item;
            }
        }

        return null;
    }

    /**
     * @param   string  $role
     * @return  boolean
     */
    public function hasRole($role)
    {
        return $this->getRole($role) ? true : false;

    }

    /**
     * Adds a Role to the ArrayCollection.
     * Can't type hint due to interface so throws RuntimeException.
     *
     * @param   Role    $role
     * @return  Group
     * @throws  \InvalidArgumentException
     */
    public function addRole($role)
    {
        if (!$role instanceof Role) {
            throw new \InvalidArgumentException('addRole takes a Role object as the parameter');
        }

        if (!$this->hasRole($role->getRole())) {
            $this->roles->add($role);
        }

        return $this;
    }

    /**
     * Pass a string, remove the Role object from collection
     *
     * @param   string  $role
     * @return  Group
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
     * Pass an array of Role objects and reset roles collection with new Roles.
     * Type hinted array due to interface.
     *
     * @param   array   $roles  Array of Role objects
     * @return  Group
     */
    public function setRoles(array $roles)
    {
        $this->roles->clear();

        foreach ($roles as $role) {
            $this->addRole($role);
        }

        return $this;
    }

    /**
     * Directly set the ArrayCollection of Roles.
     * Type hinted as Collection which is the parent of (Array|Persistent)Collection.
     *
     * @param   Collection  $collection
     * @return  Group
     */
    public function setRolesCollection(Collection $collection)
    {
        $this->roles = $collection;

        return $this;
    }
}