<?php
namespace Oro\Bundle\UserBundle\Entity\Repository;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\EntityRepository;

use Oro\Bundle\UserBundle\Entity\Group;

class GroupRepository extends EntityRepository
{
    /**
     * Get user query builder
     *
     * @param \Oro\Bundle\UserBundle\Entity\Group $group
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getUserQueryBuilder(Group $group)
    {
        return $this->_em->createQueryBuilder()
            ->select('u')
            ->from('OroUserBundle:User', 'u')
            ->join('u.groups', 'groups')
            ->where('groups = :group')
            ->setParameter('group', $group);
    }
}
