<?php

namespace Oro\Bundle\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;

use Oro\Bundle\UserBundle\Annotation\Acl as AnnotationAcl;

/**
 * @Gedmo\Tree(type="nested")
 * @ORM\Entity(repositoryClass="Oro\Bundle\UserBundle\Entity\Repository\AclRepository")
 * @ORM\Table(name="user_acl")
 */
class Acl
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=50, name="id")
     */
    protected $id;

    /**
     * @Gedmo\TreeParent
     * @ORM\ManyToOne(targetEntity="Acl", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $parent;

    /**
     * @ORM\OneToMany(targetEntity="Acl", mappedBy="parent")
     * @ORM\OrderBy({"lft" = "ASC"})
     */
    protected  $children;

    /**
     * @ORM\Column(type="string", length=250)
     */
    protected $name;

    /**
     * @ORM\Column(type="string", length=250)
     */
    protected $description;

    /**
     * @ORM\ManyToMany(targetEntity="Role", inversedBy="aclResources")
     * @ORM\JoinTable(name="user_acl_role",
     *      joinColumns={@ORM\JoinColumn(name="acl_id", referencedColumnName="id", onDelete="CASCADE")},
     *          inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id", onDelete="CASCADE")}
     *      )
     */
    protected $accessRoles;

    /**
     * @Gedmo\TreeLeft
     * @ORM\Column(name="lft", type="integer")
     */
    protected $lft;

    /**
     * @Gedmo\TreeLevel
     * @ORM\Column(name="lvl", type="integer")
     */
    protected $lvl;

    /**
     * @Gedmo\TreeRight
     * @ORM\Column(name="rgt", type="integer")
     */
    protected $rgt;

    /**
     * @Gedmo\TreeRoot
     * @ORM\Column(name="root", type="integer", nullable=true)
     */
    protected $root;

    public function __construct()
    {
        $this->accessRoles = new ArrayCollection();
        $this->children = new ArrayCollection();
    }

    /**
     * Set id
     *
     * @param string $id
     * @return Acl
     */
    public function setId($id)
    {
        $this->id = $id;
    
        return $this;
    }

    /**
     * Get id
     *
     * @return string 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Acl
     */
    public function setName($name)
    {
        $this->name = $name;
    
        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Acl
     */
    public function setDescription($description)
    {
        $this->description = $description;
    
        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set acl info
     *
     * @param \Oro\Bundle\UserBundle\Annotation\Acl $aclData
     */
    public function setData(AnnotationAcl $aclData)
    {
        $this->setName($aclData->getName());
        if ($aclData->getDescription()) {
            $this->setDescription($aclData->getDescription());
        }
    }
    
    /**
     * Add children
     *
     * @param \Oro\Bundle\UserBundle\Entity\Acl $children
     * @return Acl
     */
    public function addChildren(Acl $children)
    {
        $this->children[] = $children;
    
        return $this;
    }

    /**
     * Remove children
     *
     * @param \Oro\Bundle\UserBundle\Entity\Acl $children
     */
    public function removeChildren(Acl $children)
    {
        $this->children->removeElement($children);
    }

    /**
     * Get children
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Set parent
     *
     * @param \Oro\Bundle\UserBundle\Entity\Acl $parent
     * @return Acl
     */
    public function setParent(Acl $parent = null)
    {
        $this->parent = $parent;
    
        return $this;
    }

    /**
     * Get parent
     *
     * @return \Oro\Bundle\UserBundle\Entity\Acl 
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set lft
     *
     * @param integer $lft
     * @return Acl
     */
    public function setLft($lft)
    {
        $this->lft = $lft;
    
        return $this;
    }

    /**
     * Get lft
     *
     * @return integer 
     */
    public function getLft()
    {
        return $this->lft;
    }

    /**
     * Set lvl
     *
     * @param integer $lvl
     * @return Acl
     */
    public function setLvl($lvl)
    {
        $this->lvl = $lvl;
    
        return $this;
    }

    /**
     * Get lvl
     *
     * @return integer 
     */
    public function getLvl()
    {
        return $this->lvl;
    }

    /**
     * Set rgt
     *
     * @param integer $rgt
     * @return Acl
     */
    public function setRgt($rgt)
    {
        $this->rgt = $rgt;
    
        return $this;
    }

    /**
     * Get rgt
     *
     * @return integer 
     */
    public function getRgt()
    {
        return $this->rgt;
    }

    /**
     * Set root
     *
     * @param integer $root
     * @return Acl
     */
    public function setRoot($root)
    {
        $this->root = $root;
    
        return $this;
    }

    /**
     * Get root
     *
     * @return integer 
     */
    public function getRoot()
    {
        return $this->root;
    }

    /**
     * Get roles array for resource
     *
     * @return array
     */
    public function getAccessRolesNames()
    {
        $roles = array();
        foreach ($this->accessRoles as $role)
        {
            $roles[] = $role->getRole();
        }

        return $roles;
    }

    /**
     * Add accessRoles
     *
     * @param \Oro\Bundle\UserBundle\Entity\Role $accessRoles
     * @return Acl
     */
    public function addAccessRole(Role $accessRoles)
    {
        $this->accessRoles[] = $accessRoles;
    
        return $this;
    }

    /**
     * Remove accessRole
     *
     * @param \Oro\Bundle\UserBundle\Entity\Role $accessRole
     */
    public function removeAccessRole(Role $accessRole)
    {
        $this->accessRoles->removeElement($accessRole);
    }

    /**
     * Get accessRoles
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAccessRoles()
    {
        return $this->accessRoles;
    }
}