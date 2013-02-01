<?php
namespace Oro\Bundle\UserBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;

use Oro\Bundle\UserBundle\Entity\Role;

class AclRepository extends EntityRepository
{
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
            ->leftJoin('acl.accessRoles', 'accessRoles', Expr\Join::WITH, 'accessRoles.role = :role')
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
