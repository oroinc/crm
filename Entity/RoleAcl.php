<?php

namespace Oro\Bundle\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\UserBundle\Entity\Acl;
use Oro\Bundle\UserBundle\Entity\Role;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_role_acl", uniqueConstraints={@ORM\UniqueConstraint(columns={"role_id", "acl_id"})})
 */
class RoleAcl
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Role")
     * @ORM\JoinColumn(name="role_id", referencedColumnName="id")
     */
    protected $role;

    /**
     * @ORM\ManyToOne(targetEntity="Acl", inversedBy="accessRoles")
     * @ORM\JoinColumn(name="acl_id", referencedColumnName="id")
     */
    protected $aclResource;

    /**
     * @ORM\Column(type="boolean", name="access")
     */
    protected $access;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set role
     *
     * @param \Oro\Bundle\UserBundle\Entity\Role $role
     * @return RoleAcl
     */
    public function setRole(Role $role = null)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get role
     *
     * @return \Oro\Bundle\UserBundle\Entity\Role
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set access
     *
     * @param boolean $access
     * @return RoleAcl
     */
    public function setAccess($access)
    {
        $this->access = $access;
    
        return $this;
    }

    /**
     * Get access
     *
     * @return boolean 
     */
    public function getAccess()
    {
        return $this->access;
    }

    /**
     * Set aclResource
     *
     * @param \Oro\Bundle\UserBundle\Entity\Acl $aclResource
     * @return RoleAcl
     */
    public function setAclResource(Acl $aclResource = null)
    {
        $this->aclResource = $aclResource;
    
        return $this;
    }

    /**
     * Get aclResource
     *
     * @return \Oro\Bundle\UserBundle\Entity\Acl 
     */
    public function getAclResource()
    {
        return $this->aclResource;
    }
}