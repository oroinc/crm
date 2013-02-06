<?php
namespace Oro\Bundle\UserBundle\Entity\Repository;

use Doctrine\ORM\Query\Expr;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Gedmo\Exception\InvalidArgumentException;
use Gedmo\Tool\Wrapper\EntityWrapper;

use Oro\Bundle\UserBundle\Entity\Role;

class AclRepository extends NestedTreeRepository
{
    public function getResourceWithRoleAccess($resourceId, Role $role)
    {
        return $this->createQueryBuilder('acl')
            //->leftJoin('acl.accessRoles', 'accessRoles')
            ->where('acl.id = :resourceId')
            //->setParameter('role', $role)
            ->setParameter('resourceId', $resourceId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Get the Tree path query builder with roles by given $node
     *
     * @param object $node
     * @throws InvalidArgumentException - if input is not valid
     * @return array
     */
    public function getPathWithRoles($node)
    {
        $meta = $this->getClassMetadata();
        if (!$node instanceof $meta->name) {
            throw new InvalidArgumentException("Node is not related to this repository");
        }
        $config = $this->listener->getConfiguration($this->_em, $meta->name);
        $wrapped = new EntityWrapper($node, $this->_em);
        if (!$wrapped->hasValidIdentifier()) {
            throw new InvalidArgumentException("Node is not managed by UnitOfWork");
        }
        $left = $wrapped->getPropertyValue($config['left']);
        $right = $wrapped->getPropertyValue($config['right']);
        $qb = $this->_em->createQueryBuilder();
        $qb->select(array('node', 'role'))
            ->from($config['useObjectClass'], 'node')
            ->leftJoin('node.accessRoles', 'role')
            ->where($qb->expr()->lte('node.'.$config['left'], $left))
            ->andWhere($qb->expr()->gte('node.'.$config['right'], $right))
            ->orderBy('node.' . $config['left'], 'ASC')
        ;
        if (isset($config['root'])) {
            $rootId = $wrapped->getPropertyValue($config['root']);
            $qb->andWhere($rootId === null ?
                    $qb->expr()->isNull('node.'.$config['root']) :
                    $qb->expr()->eq('node.'.$config['root'], is_string($rootId) ? $qb->expr()->literal($rootId) : $rootId)
            );
        }

        return $qb->getQuery()->getResult();
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
