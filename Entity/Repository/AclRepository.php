<?php
namespace Oro\Bundle\UserBundle\Entity\Repository;

use Doctrine\ORM\Query\Expr;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

use Oro\Bundle\UserBundle\Entity\Role;
use Oro\Bundle\UserBundle\Entity\Acl;

class AclRepository extends NestedTreeRepository
{
    /**
     * Get full node list with roles for acl resource
     *
     * @param \Oro\Bundle\UserBundle\Entity\Acl $acl
     * @return array
     */
    public function getFullNodeWithRoles(Acl $acl)
    {
        return $this->createQueryBuilder('acl')
            ->select(array('acl', 'role'))
            ->leftJoin('acl.accessRoles', 'role')
            ->where('acl.rgt > :left_key')
            ->andWhere('acl.lft < :right_key')
            ->orderBy('acl.lft')
            ->setParameter('left_key', $acl->getLft())
            ->setParameter('right_key', $acl->getRgt())
            ->getQuery()
            ->getResult();
    }

    /**
     * Get Acl array for role
     *
     * @param \Oro\Bundle\UserBundle\Entity\Role $role
     *
     * @return array
     */
    public function getRoleAcl(Role $role)
    {
        return $this->createQueryBuilder('acl')
            ->select('acl', 'accessRoles')
            ->leftJoin('acl.accessRoles', 'accessRoles', Expr\Join::WITH, 'accessRoles.id = :role')
            ->setParameter('role', $role)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get Acl tree for role
     *
     * @param \Oro\Bundle\UserBundle\Entity\Role $role
     *
     * @return array
     */
    public function getRoleAclTree(Role $role)
    {
        return $this->toTree($this->getRoleAcl($role));
    }

    /**
     * Convert to tree array
     *
     * @param array $aclRecords
     * @param null  $parent
     *
     * @return array
     */
    protected function toTree(array $aclRecords, $parent = null)
    {
        $branch = array();
        foreach ($aclRecords as $element) {
            if ($element->getParent() == $parent) {
                $children = $this->toTree($aclRecords, $element->getId());
                if ($children) {
                    $element->addChildren($children);
                }
                $branch[$element->getId()] = $element;
            }
        }

        return $branch;
    }
}
