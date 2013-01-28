<?php

namespace Oro\Bundle\UserBundle\Entity;

use Symfony\Component\Security\Core\Role\RoleInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Role Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="access_role")
 * @UniqueEntity("role")
 */
class Role implements RoleInterface
{

    /**
     * @ORM\Id
     * @ORM\Column(type="smallint", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", unique=true, length=30, nullable=false)
     * @Assert\MaxLength(30)
     * @Assert\Regex("/^[a-zA-Z0-9_]+$/")
     */
    protected $role;

    /**
     * @ORM\Column(type="string", length=30)
     * @Assert\MaxLength(30)
     */
    protected $label;

    /**
     * Populate the role field
     *
     * @param string $role ROLE_FOO etc
     */
    public function __construct($role = '')
    {
        $this->role  =
        $this->label = $role;
    }

    /**
     * Return the role id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Return the role name field
     *
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Return the role label field
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set role name only for newly created role
     *
     * @param   string  $role  Role name
     * @return  Role
     * @throws  \RuntimeException
     */
    public function setRole($role)
    {
        if (!is_null($this->id)) {
            throw new \RuntimeException('Unable to change role name at runtime');
        }

        $this->role = (string) strtoupper($role);

        // every role should be prefixed with 'ROLE_'
        if (strpos($this->role, 'ROLE_') !== 0) {
            $this->role = 'ROLE_' . $this->role;
        }

        return $this;
    }

    /**
     * Set the new label for role
     *
     * @param   string  $label  New label
     * @return  Role
     */
    public function setLabel($label)
    {
        $this->label = (string) $label;

        return $this;
    }

    /**
     * Return the role name field
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->role;
    }
}