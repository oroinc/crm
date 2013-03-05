<?php
namespace Oro\Bundle\UserBundle\Entity\Repository;

use Doctrine\ORM\Query\Expr;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;

use Oro\Bundle\UserBundle\Entity\Role;
use Oro\Bundle\UserBundle\Entity\Acl;

class AclRepository extends NestedTreeRepository
{

    /**
     * Get array with allowed acl resources for role array
     *
     * @param  array                                $roles
     * @return \Oro\Bundle\UserBundle\Entity\Role[] $roles
     */
    public function getAllowedAclResourcesForUserRoles(array $roles)
    {
        $allowedAcl = array();
        $qb = $this->createQueryBuilder('acl')
            ->select('acl.id')
            ->where('acl.rgt > :left_key')
            ->andWhere('acl.lft < :right_key')
            ->orderBy('acl.lft')
        ;

        foreach ($roles as $role) {
            $aclList = $role->getAclResources();
            if (count($aclList)) {

                foreach ($aclList as $acl) {
                    $aclList = $qb->setParameter('left_key', $acl->getLft())
                        ->setParameter('right_key', $acl->getRgt())
                        ->getQuery()
                        ->getScalarResult();
                    $acls = array();
                    foreach ($aclList as $scalar) {
                        $acls[] = $scalar['id'];
                    }

                    $allowedAcl = array_unique(
                        array_merge(
                            $allowedAcl,
                            $acls
                        )
                    );
                }
            }
        }

        return $allowedAcl;
    }

    /**
     * Get full node list with roles for acl resource
     *
     * @param  \Oro\Bundle\UserBundle\Entity\Acl   $acl
     * @return \Oro\Bundle\UserBundle\Entity\Acl[]
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
     * @param string $aclId
     * @return array
     */
    public function getAclRolesWithoutTree($aclId)
    {
        return  $this->getEntityManager()->createQueryBuilder('acl')
            ->select('role.role')
            ->from('OroUserBundle:role', 'role')
            ->join('role.aclResources', 'acl')
            ->where('acl.id = :aclId')
            ->setParameter('aclId', $aclId)
            ->getQuery()
            ->getArrayResult();
    }

    /**
     * Get Acl array for role
     *
     * @param \Oro\Bundle\UserBundle\Entity\Role $role
     *
     * @return array
     */
    public function getAclListWithRoles(Role $role)
    {
        return $this->createQueryBuilder('acl')
            ->select('acl', 'accessRoles')
            ->leftJoin('acl.accessRoles', 'accessRoles', Expr\Join::WITH, 'accessRoles.id = :role')
            ->setParameter('role', $role)
            ->orderBy('acl.root, acl.lft', 'ASC')
            ->getQuery()
            ->getArrayResult();
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
        return $this->buildTree($this->getAclListWithRoles($role));
    }
}
