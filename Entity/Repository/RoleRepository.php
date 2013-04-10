<?php
namespace Oro\Bundle\UserBundle\Entity\Repository;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\EntityRepository;

use Oro\Bundle\UserBundle\Entity\Role;

class RoleRepository extends EntityRepository
{
    /**
     * Get user query builder
     *
     * @param \Oro\Bundle\UserBundle\Entity\Role $role
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getUserQueryBuilder(Role $role)
    {
        return $this->_em->createQueryBuilder()
            ->select('u')
            ->from('OroUserBundle:User', 'u')
            ->join('u.roles', 'role')
            ->where('role.id = :role')
            ->setParameter('role', $role);
    }
}
